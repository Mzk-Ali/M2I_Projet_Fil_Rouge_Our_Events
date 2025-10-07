<?php

namespace App\Controller;

use App\Entity\Premise;
use App\Repository\PremiseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PremiseController extends AbstractController
{
    #[Route('/premise', name: 'app_premise')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PremiseController.php',
        ]);
    }


    /**
     * Cette méthode permet de récupérer l'ensemble des locaux.
     *
     * @param PremiseRepository $premiseRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/premises', name: 'api_get_premise', methods: ['GET'])]
    public function getPremiseList(PremiseRepository $premiseRepository, SerializerInterface $serializer): JsonResponse
    {
        $premiseList = $premiseRepository->findAll();
        $jsonPremiseList = $serializer->serialize($premiseList, 'json');

        return new JsonResponse($jsonPremiseList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet d'insérer un nouveau local.
     * Exemple de données :
     * {
     *     "address": "45 Avenue des Champs",
     *     "city": "Lyon",
     *     "postal_code": "69000"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un nouveau local')]
    #[Route('/api/premises', name: 'api_create_premise', methods: ['POST'])]
    public function createPremise(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $premise = $serializer->deserialize($request->getContent(), Premise::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($premise);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($premise);
        $em->flush();

        $jsonCategory = $serializer->serialize($premise, 'json');

        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }

    /**
     * Cette méthode permet de mettre à jour un local en fonction de son id.
     *
     * Exemple de données :
     * {
     *     "address": "45 Avenue des Champs",
     *     "city": "Lyon",
     *     "postal_code": "69000"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Premise $currentPremise
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un local')]
    #[Route('/api/premises/{id}', name: 'api_update_premise', methods: ['PUT'])]
    public function updatePremise(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, Premise $currentPremise, ValidatorInterface $validator): JsonResponse
    {
        $updatedPremise = $serializer->deserialize($request->getContent(), Premise::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentPremise]);

        // On vérifie les erreurs
        $errors = $validator->validate($updatedPremise);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updatedPremise);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de supprimer un local par rapport à son id.
     *
     * @param Premise $premise
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un local')]
    #[Route('/api/premises/{id}', name: 'api_delete_premise', methods: ['DELETE'])]
    public function deleteCategory(Premise $premise, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($premise);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
