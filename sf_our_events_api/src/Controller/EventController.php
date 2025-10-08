<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
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

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EventController.php',
        ]);
    }


    /**
     * Cette méthode permet de récupérer l'ensemble des évenements.
     *
     * @param EventRepository $eventRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/events', name: 'api_get_events', methods: ['GET'])]
    public function getEventList(Request $request, EventRepository $eventRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $category = $request->get('category');
        $city = $request->get('city');

        $categoryId = !empty($category) ? (int) $category : null;
        $city = !empty($city) ? $city : null;

        $result = $eventRepository->findAllWithPagination($page, $limit, $categoryId, $city);
        $events = $result['data'];
        // $total  = $result['total'];
        $jsonEventList = $serializer->serialize($events , 'json', ['groups' => 'getEvents']);

        return new JsonResponse($jsonEventList, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet d'insérer un nouveau évenement .
     * Exemple de données :
     * {
     *     "title": "Concert Jazz Live",
     *     "description": "Un concert exceptionnel avec les meilleurs musiciens de jazz.",
     *     "image_url": "https://example.com/images/jazz.jpg",
     *     "capacity": 150,
     *     "start_datetime": "+10 days",
     *     "end_datetime": "+10 days +2 hours",
     *     "premiseId": 2,
     *     "categories": [35]
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param CategoryRepository $categoryRepository
     * @param PremiseRepository $premiseRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un Evenement')]
    #[Route('/api/events', name: 'api_create_event', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, CategoryRepository $categoryRepository, PremiseRepository $premiseRepository, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = $serializer->deserialize($request->getContent(), Event::class, 'json', ['groups' => 'event:write', 'ignore_attributes' => ['categories']]);

        $premiseId = $data["premiseId"];

        $premise = $premiseRepository->find((int) $premiseId);

        $manager = $this->getUser();

        if (!$premise) {
            return new JsonResponse(["error" => "Le lieu avec l'id $premiseId n'existe pas."], Response::HTTP_BAD_REQUEST);
        }

        // Suppression des catégories fantômes ajoutés par le serializer
        $event->getCategories()->clear();

        // Gestion des catégories via leurs IDs
        if (!empty($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $category = $categoryRepository->find((int) $categoryId);
                if ($category) {
                    $event->addCategory($category);
                }
            }
        }

        $event->setPremise($premise);
        $event->setManager($manager);

        // On vérifie les erreurs
        $errors = $validator->validate($event);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($event);
        $em->flush();

        $jsonEvent = $serializer->serialize($event, 'json', ['groups' => 'event:read']);

        return new JsonResponse($jsonEvent, Response::HTTP_CREATED, [], true);
    }


    /**
     * Cette méthode permet de mettre à jour un évenement en fonction de son id.
     *
     * Exemple de données :
     * {
     *     "title": "Concert Jazz Live",
     *     "description": "Un concert exceptionnel avec les meilleurs musiciens de jazz.",
     *     "image_url": "https://example.com/images/jazz.jpg",
     *     "capacity": 150,
     *     "start_datetime": "+10 days",
     *     "end_datetime": "+10 days +2 hours",
     *     "premiseId": 2,
     *     "categories" : [35]
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Event $currentEvent
     * @param EntityManagerInterface $em
     * @param CategoryRepository $categoryRepository
     * @param PremiseRepository $premiseRepository
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un Evenement')]
    #[Route('/api/events/{id}', name: 'api_update_event', methods: ['PUT'])]
    public function updateEvent(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, Event $currentEvent, ValidatorInterface $validator, CategoryRepository $categoryRepository, PremiseRepository $premiseRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $updatedEvent = $serializer->deserialize($request->getContent(), Event::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEvent]);

        $premiseId = $data["premiseId"];

        $premise = $premiseRepository->find((int) $premiseId);

        if (!$premise) {
            return new JsonResponse(["error" => "Le lieu avec l'id $premiseId n'existe pas."], Response::HTTP_BAD_REQUEST);
        }


        // Suppression des catégories fantômes ajoutés par le serializer
        $updatedEvent->getCategories()->clear();

        // Gestion des catégories via leurs IDs
        if (!empty($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $category = $categoryRepository->find((int) $categoryId);
                if ($category) {
                    $updatedEvent->addCategory($category);
                }
            }
        }

        $updatedEvent->setPremise($premise);

        // On vérifie les erreurs
        $errors = $validator->validate($updatedEvent);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updatedEvent);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    /**
     * Cette méthode permet de supprimer un évenement par rapport à son id.
     *
     * @param Event $event
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un Evenement')]
    #[Route('/api/events/{id}', name: 'api_delete_event', methods: ['DELETE'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em): JsonResponse
    {
        // Suppression des liens avec les catégories de cette évenement
        foreach ($event->getCategories() as $category) {
            $event->removeCategory($category);
        }
        $em->remove($event);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Cette méthode permet à un utilisateur de s'inscrire à un évenement.'
     *
     * @param Event $event
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_USER', message: "Vous devez être connecté pour s'inscrire à un evenement")]
    #[Route('/api/events/{id}/register', name: 'api_register_event', methods: ['POST'])]
    public function registerMyselfToEvent(Event $event, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

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

        return new JsonResponse(['message' => "Inscription à l'évenement réussie"], Response::HTTP_OK);
    }


    /**
     * Cette méthode permet à un utilisateur de se désinscrire à un évenement.'
     *
     * @param Event $event
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_USER', message: "Vous devez être connecté pour s'inscrire à un evenement")]
    #[Route('/api/events/{id}/unregister', name: 'api_unregister_event', methods: ['POST'])]
    public function unregisterMyselfToEvent(Event $event, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

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

        return new JsonResponse(['message' => 'Désinscription réussie'], Response::HTTP_OK);
    }
}
