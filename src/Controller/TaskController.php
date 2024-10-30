<?php

namespace App\Controller;

use App\Entity\TaskEntity;
use App\Repository\TaskEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// Le contrôleur TaskController gère les requêtes HTTP liées aux tâches.
class TaskController extends AbstractController
{
    private TaskEntityRepository $taskRepository;
    private ValidatorInterface $validator;

    // Constructeur de la classe, injectant le repository et le serializer.
    public function __construct(TaskEntityRepository $taskRepository, private SerializerInterface $serializer, ValidatorInterface $validator)
    {
        // Initialise le repository des tâches et le serializer pour la classe.
        $this->taskRepository = $taskRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    // Route pour récupérer toutes les tâches. Accessible via l'URL '/' avec la méthode GET.
    #[Route('/', name: 'getAllTasks', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Récupère toutes les tâches en utilisant la méthode `getAllTasks` du repository.
        $allTasks = $this->taskRepository->getAllTasks();

        // Sérialise les objets `TaskEntity` en format JSON pour pouvoir les renvoyer au client.
        $tasksEncoded = $this->serializer->serialize($allTasks, 'json');

        // Retourne les tâches sérialisées dans une réponse JSON avec le statut HTTP 200 (OK).
        // Le dernier paramètre `true` indique que les données sont déjà en JSON, donc pas besoin de ré-encodage.
        return new JsonResponse($tasksEncoded, Response::HTTP_OK, [], true);
    }

    // Route pour créer une nouvelle tâche. Accessible via l'URL '/creation' avec la méthode POST.
    #[Route('/creation', name: 'app_task', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Décode le contenu JSON de la requête pour obtenir un tableau associatif.
        // $newTaskData = json_decode($request->getContent(), true);

        // // Crée une nouvelle instance de `TaskEntity` pour la nouvelle tâche.
        // $tasks = new TaskEntity();
        // $tasks->setTitle($newTaskData['title']);
        // $tasks->setDescription($newTaskData['description']);
        // Décode et désérialise le contenu JSON de la requête pour créer un objet TaskEntity.
        //---------------------------------------------------------------- Autres Methode ----------------------------------------------------------//

        // Désérialise le contenu JSON de la requête pour le convertir en une instance de TaskEntity.
        // La méthode 'deserialize' prend trois paramètres :
        // - $request->getContent() : le contenu brut de la requête HTTP en format JSON, qui contient les données de la tâche à créer.
        // - TaskEntity::class : la classe cible dans laquelle les données JSON seront converties.
        // - 'json' : le format de la donnée entrante, indiquant que les données sont en JSON.
        //
        // En résultat, $task sera une nouvelle instance de TaskEntity avec les propriétés remplies
        // à partir des données JSON reçues dans la requête.
        $task = $this->serializer->deserialize($request->getContent(), TaskEntity::class, 'json');

        // Valide les données de la tâche nouvellement créée en utilisant les règles de validation de TaskEntity.
        $errors = $this->validator->validate($task);

        // Vérifie si des erreurs de validation existent.
        if (count($errors) > 0) {
            // En cas d'erreurs, renvoie une réponse JSON avec la liste des erreurs
            // et un statut HTTP 400 (BAD REQUEST) pour signaler le problème.
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        // Si la validation est réussie, enregistre la tâche dans la base de données via le repository.
        // $this->taskRepository->save($task);

        // utilisation de la function createOneTasks
        $this->taskRepository->createOneTask($task);

        // Retourne une réponse JSON confirmant la création de la tâche avec un statut HTTP 201 (CREATED).
        return new JsonResponse(["message" => "task created"], Response::HTTP_OK);
    }

    #[Route('/supprimer/{id}', name: 'deleteTask', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            // Appelle la méthode du repository pour supprimer une tâche en fonction de son identifiant.
            $deleteTasks = $this->taskRepository->deleteOneTasks($id); // Enlève `int` ici

            // Vérifie si la tâche a bien été supprimée (par exemple, si la méthode `deleteOneTask` retourne un booléen).
            if (!$deleteTasks) {
                return new JsonResponse(["message" => "task not found"], Response::HTTP_NOT_FOUND);
            }

            // Retourne une réponse confirmant la suppression de la tâche avec un code HTTP 204 (No Content).
            return new JsonResponse(["message" => "task deleted"], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/modifier/{id}', name: 'updateTask', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {

        try {
            // Décode le contenu JSON de la requête pour obtenir les nouvelles données de la tâche.
            // $modifyData = json_decode($request->getContent(), true);

            // // Récupère les nouvelles valeurs pour le titre et la description.
            // $newTitle = $modifyData['title'] ?? null; // Utilise null si non défini
            // $newDescription = $modifyData['description'] ?? null; // Utilise null si non défini

            //----------------------- $autres moyen ---------------------------------

            $modifiedTask = $this->serializer->deserialize($request->getContent(), TaskEntity::class, 'json');

            $errors = $this->validator->validate($modifiedTask);

            // Vérifie si des erreurs de validation existent.
            if (count($errors) > 0) {
                // En cas d'erreurs, renvoie une réponse JSON avec la liste des erreurs
                // et un statut HTTP 400 (BAD REQUEST) pour signaler le problème.
                return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
            }
            // Appelle la méthode updateOneTasks pour mettre à jour la tâche.
            $isUpdated = $this->taskRepository->updateOneTasks($id, $modifiedTask);

            return new JsonResponse(["message" => "Tache modifier avec succes"], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
