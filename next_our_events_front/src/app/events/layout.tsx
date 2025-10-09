import { Metadata } from "next";

export const metadata: Metadata = {
    title: "OurEvents - Liste des Evénements Publics",
    description: "Il s'agit de la page des événements publics de OurEvents",
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