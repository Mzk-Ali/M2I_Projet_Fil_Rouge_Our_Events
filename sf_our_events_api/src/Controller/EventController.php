<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
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

        $eventList = $eventRepository->findAllWithPagination($page, $limit);
        $jsonEventList = $serializer->serialize($eventList, 'json');

        return new JsonResponse($jsonEventList, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet d'insérer un nouveau évenement .
     * Exemple de données :
     * {
     *     "title": "",
     *     "description": "",
     *     "image_url": "",
     *     "capacity": "",
     *     "start_datetime": "",
     *     "end_datetime": ""
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un Evenement')]
    #[Route('/api/events', name: 'api_create_event', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $event = $serializer->deserialize($request->getContent(), Event::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($event);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($event);
        $em->flush();

        $jsonEvent = $serializer->serialize($event, 'json');

        return new JsonResponse($jsonEvent, Response::HTTP_CREATED, [], true);
    }


    /**
     * Cette méthode permet de mettre à jour un évenement en fonction de son id.
     *
     * Exemple de données :
     * {
     *     "title": "",
     *     "description": "",
     *     "image_url": "",
     *     "capacity": "",
     *     "start_datetime": "",
     *     "end_datetime": ""
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Event $currentEvent
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un Evenement')]
    #[Route('/api/events/{id}', name: 'api_update_event', methods: ['PUT'])]
    public function updateEvent(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, Event $currentEvent, ValidatorInterface $validator): JsonResponse
    {
        $updatedEvent = $serializer->deserialize($request->getContent(), Event::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEvent]);

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
        $em->remove($event);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
