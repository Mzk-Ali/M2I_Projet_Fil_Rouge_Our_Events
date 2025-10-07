<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
final class UserController extends AbstractController
{
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
}
