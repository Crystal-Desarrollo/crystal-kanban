const { Client, GatewayIntentBits } = require('discord.js');
const fetch = require('node-fetch'); // node-fetch v2
require('dotenv').config();

const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.MessageContent,
  ],
});

client.once('ready', () => {
  console.log(`✅ Bot logueado como ${client.user.tag}`);
});

client.on('messageCreate', async message => {
  if (message.author.bot) return;
  if (!message.content.startsWith('!kanban')) return;

  try {
    // Local only: Disable SSL verification for local development
    process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

    const response = await fetch(`${process.env.API_URL}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: message.author.username,
        content: message.content.replace('!kanban', '').trim(),
      }),
    });

    const data = await response.json();

    if (response.ok) {
      await message.channel.send({
        embeds: [
          {
            title: '✅ Tarea creada',
            fields: [
              { name: 'Proyecto', value: data.task.project.name },
              { name: 'Título', value: data.task.name },
              {
                name: 'Usuario asignado',
                value: data.task.assigned_to_user?.name || 'Sin asignar',
              },
            ],
          },
        ],
      });
    } else {
      await message.channel.send(
        `❌ Error al crear la tarea: ${data.message || 'Error desconocido'}`
      );
    }
  } catch (err) {
    await message.channel.send(`❌ Error al crear la tarea: ${err.message}`);
  }
});

client.login(process.env.DISCORD_BOT_TOKEN);
