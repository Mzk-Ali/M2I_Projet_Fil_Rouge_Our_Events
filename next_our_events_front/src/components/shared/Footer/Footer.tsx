import Link from "next/link"
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome"
import { faFacebook, faInstagram, faTwitter } from "@fortawesome/free-brands-svg-icons"
import Image from "next/image"

export default function Footer() {
    return(
        <footer className="w-full font-inter flex flex-col gap-2.5 border-t border-t-secondary-2 text-sm md:text-lg">
            <section className="flex flex-col gap-5 md:flex-row md:justify-around md:items-center md:px-20 md:py-2.5">
                <div className="flex flex-col items-center gap-5 p-2.5 text-center md:w-[500px] md:text-start md:items-start">
                    <Image 
                        src="/img/logo_ourEvents.png" 
                        alt="Logo OurEvents" 
                        width={200} 
                        height={70} 
                    />
                    <p className="font-light">Avec OurEvents, trouvez et rejoignez les événements qui vous ressemblent, selon vos envies et votre ville.</p>
                    <ul className="flex justify-center items-center lg:mx-auto">
                        <li>
                            <Link href="#" className="flex flex-col items-center gap-2 p-5">
                                <FontAwesomeIcon icon={faFacebook} className="size-5" />
                                Facebook
                            </Link>
                        </li>
                        <li>
                            <Link href="#" className="flex flex-col items-center gap-2 p-5">
                                <FontAwesomeIcon icon={faInstagram} className="size-5" />
                                Instagram
                            </Link>
                        </li>
                        <li>
                            <Link href="#" className="flex flex-col items-center gap-2 p-5">
                                <FontAwesomeIcon icon={faTwitter} className="size-5" />
                                Twitter
                            </Link>
                        </li>
                    </ul>
                </div>
                <div className="flex justify-center">
                    <ul className="w-[200px] flex flex-col items-start">
                        <li className="w-full px-3.5 py-3">
                            <Link href="/events">Événements</Link>
                        </li>
                        <li className="w-full px-3.5 py-3">
                            <Link href="/admin">Panel Admin</Link>
                        </li>
                        <li className="w-full px-3.5 py-3">
                            <Link href="/login">Se Connecter</Link>
                        </li>
                        <li className="w-full px-3.5 py-3">
                            <Link href="/register">S'inscrire</Link>
                        </li>
                    </ul>
                </div>
            </section>
            <hr className="w-11/12 mx-auto" />
            <section className="flex flex-col items-center gap-2.5 py-2.5 text-[12px] font-normal md:flex-row md:justify-around lg:text-base">
                <p>Copyright 2025 &copy; OurEvents, ALL Rights Reserved</p>
                <div className="flex gap-5 p-2.5 underline underline-offset-2">
                    <a href="#">Politique de Confidentialité</a>
                    <a href="#">Termes et Conditions</a>
                </div>
            </section>
        </footer>
    )
};