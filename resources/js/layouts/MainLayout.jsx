import { Head } from "@inertiajs/react";
import { AppShell } from "@mantine/core";
import NavBarNested from "@/layouts/NavBarNested";

export default function MainLayout({ children, title }) {
  return (
    <AppShell
      navbar={{ width: 300, breakpoint: "sm", collapsed: { mobile: false } }}
      style={{ padding: "3rem 5rem" }}
    >
      <Head title={title} />

      <AppShell.Navbar>
        <NavBarNested></NavBarNested>
      </AppShell.Navbar>

      <AppShell.Main>{children}</AppShell.Main>
    </AppShell>
  );
}
