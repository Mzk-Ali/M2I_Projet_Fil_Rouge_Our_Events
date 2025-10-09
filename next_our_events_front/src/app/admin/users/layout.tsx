import { Metadata } from "next";

export const metadata: Metadata = {
    title: "OurEvents - Panel d'Administration des Utilisateurs",
    description: "Il s'agit de la page d'administration des utilisateurs de OurEvents",
};


export default function AppLayout({
    children,
  }: {
    children: React.ReactNode
  }) {
    return (
        <>
            {children}
        </>
    )
  }