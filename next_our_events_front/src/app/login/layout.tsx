import { Metadata } from "next";

export const metadata: Metadata = {
    title: "OurEvents - Page de Connexion",
    description: "Il s'agit de la page de connexion à OurEvents",
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