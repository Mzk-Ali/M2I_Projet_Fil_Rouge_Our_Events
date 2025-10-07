<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CategoryController.php',
        ]);
    }


    /**
     * Cette méthode permet de récupérer l'ensemble des catégories.
     *
     * @param CategoryRepository $categoryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/categories', name: 'api_get_category', methods: ['GET'])]
    public function getCategoryList(CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $categoryList = $categoryRepository->findAll();
        $jsonCategoryList = $serializer->serialize($categoryList, 'json');

        return new JsonResponse($jsonCategoryList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet d'insérer une nouvelle catégorie.
     * Exemple de données :
     * {
     *     "name": "Sport"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une Catégorie')]
    #[Route('/api/categories', name: 'api_create_category', methods: ['POST'])]
    public function createCategory(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($category);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($category);
        $em->flush();

        $jsonCategory = $serializer->serialize($category, 'json');

        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }


    /**
     * Cette méthode permet de mettre à jour une catégorie en fonction de son id.
     *
     * Exemple de données :
     * {
     *     "name": "Sport"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Category $currentCategory
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une Catégorie')]
    #[Route('/api/categories/{id}', name: 'api_update_category', methods: ['PUT'])]
    public function updateCategory(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, Category $currentCategory, ValidatorInterface $validator): JsonResponse
    {
        $updatedCategory = $serializer->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]);

        // On vérifie les erreurs
        $errors = $validator->validate($updatedCategory);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updatedCategory);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de supprimer une catégorie par rapport à son id.
     *
     * @param Category $category
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une Catégorie')]
    #[Route('/api/categories/{id}', name: 'api_delete_category', methods: ['DELETE'])]
    public function deleteCategory(Category $category, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
