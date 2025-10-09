import { Metadata } from "next";

export const metadata: Metadata = {
    title: "OurEvents - Page d'Inscription à OurEvents",
    description: "Il s'agit de la page d'inscription à OurEvents",
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