import { Metadata } from "next";

export const metadata: Metadata = {
    title: "OurEvents - Panel d'Administration",
    description: "Il s'agit de la page d'administration de OurEvents",
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