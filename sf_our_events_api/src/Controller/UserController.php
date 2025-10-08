<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users')]
final class UserController extends AbstractController
{

    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs.
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour récupérer la liste des utilisateurs')]
    #[Route('', name: 'api_get_users', methods: ['GET'])]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($users , 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }




    /**
     * Cette méthode permet d'ajouter ou de retirer le rôle Admin d'un utilisateur'
     *
     * @param User $user
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une Catégorie')]
    #[Route('/{id}/toggle-admin', name: 'api_user_toggle_admin', methods: ['PATCH'])]
    public function toggleAdminRole(User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->getId() === $this->getUser()->getId()) {
            return new JsonResponse(['message' => 'Impossible de modifier vos propres rôles.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            // Retirer le rôle ADMIN
            $roles = array_filter($roles, fn($r) => $r !== 'ROLE_ADMIN');
            $action = 'retiré';
        } else {
            // Ajouter le rôle ADMIN
            $roles[] = 'ROLE_ADMIN';
            $action = 'ajouté';
        }

        $user->setRoles(array_values($roles));
        $em->flush();

        return new JsonResponse([
            'message' => "Rôle ADMIN $action pour l'utilisateur {$user->getEmail()}",
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }


    /**
     * Cette méthode permet à un admin d'inscrire un utilisateur à un évenement'
     *
     * @param int $userId
     * @param int $eventId
     * @param EventRepository $eventRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: "Vous devez avoir le rôle Admin pour inscrire un utilisateur à un evenement")]
    #[Route('/{userId}/register/{eventId}', name: 'api_admin_register_user_to_event', methods: ['POST'])]
    public function adminRegisterUserToEvent(int $userId, int $eventId, EventRepository $eventRepository, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($userId);
        $event = $eventRepository->find($eventId);

        $response = match (true) {
            !$user => new JsonResponse(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND),
            !$event => new JsonResponse(['message' => 'Evénement introuvable'], Response::HTTP_NOT_FOUND),
            $user->getRegisteredEvents()->contains($event) => new JsonResponse(['message' => 'Utilisateur déjà inscrit'], Response::HTTP_CONFLICT),
            default => null
        };

        if ($response) {
            return $response;
        }

        $user->addRegisteredEvent($event);
        $em->flush();

        return new JsonResponse(['message' => "Inscription de l'utilisateur à l'évenement réussie"], Response::HTTP_OK);
    }

    /**
     * Cette méthode permet à un admin de désinscrire un utilisateur à un évenement'
     *
     * @param int $userId
     * @param int $eventId
     * @param EventRepository $eventRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: "Vous devez avoir le rôle Admin pour désinscrire un utilisateur à un evenement")]
    #[Route('/{userId}/unregister/{eventId}', name: 'api_admin_unregister_user_to_event', methods: ['POST'])]
    public function adminUnregisterUserToEvent(int $userId, int $eventId, EventRepository $eventRepository, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($userId);
        $event = $eventRepository->find($eventId);

        $response = match (true) {
            !$user => new JsonResponse(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND),
            !$event => new JsonResponse(['message' => 'Evénement introuvable'], Response::HTTP_NOT_FOUND),
            !$user->getRegisteredEvents()->contains($event) => new JsonResponse(['message' => 'Utilisateur non inscrit'], Response::HTTP_BAD_REQUEST),
            default => null
        };

        if ($response) {
            return $response;
        }

        $user->removeRegisteredEvent($event);
        $em->flush();

        return new JsonResponse(['message' => "Désinscription de l'utilisateur réussie"], Response::HTTP_OK);
    }
}
