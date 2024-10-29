<?php

namespace App\Repository;

use App\Entity\TaskEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Classe TaskEntityRepository qui étend ServiceEntityRepository pour fournir des méthodes personnalisées
 * permettant de manipuler des objets TaskEntity dans la base de données.
 *
 * @extends ServiceEntityRepository<TaskEntity>
 */
class TaskEntityRepository extends ServiceEntityRepository
{
    // Constructeur injectant les dépendances ManagerRegistry et EntityManagerInterface.
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        // Initialise le repository avec le ManagerRegistry et spécifie que ce repository est pour TaskEntity.
        parent::__construct($registry, TaskEntity::class);
        $this->em = $em; // Initialise l'EntityManager pour permettre les opérations de base de données.
    }

    /**
     * Enregistre une entité TaskEntity dans la base de données.
     *
     * @param TaskEntity $task L'entité de la tâche à enregistrer.
     */
    public function save(TaskEntity $task): void
    {
        // Persiste l'objet TaskEntity, préparant ainsi l'entité pour l'insertion ou la mise à jour dans la base de données.
        $this->em->persist($task);
        // Exécute les modifications en base de données (insertion/mise à jour).
        $this->em->flush();
    }

    /**
     * Récupère toutes les tâches stockées dans la base de données en sélectionnant uniquement
     * les champs 'title' et 'description'.
     *
     * @return array Un tableau contenant les titres et descriptions de toutes les tâches.
     */
    public function getAllTasks(): array
    {
        // Obtient le repository pour l'entité TaskEntity pour interagir directement avec la table de tâches.
        $repository = $this->em->getRepository(TaskEntity::class);

        // Crée un QueryBuilder pour construire une requête personnalisée.
        // Ici, on sélectionne uniquement les champs 'title' et 'description' de chaque tâche.
        $queryBuilder = $repository->createQueryBuilder('t')
            ->select('t.id', 't.title', 't.description')
            ->getQuery();

        // Exécute la requête pour obtenir les résultats sous forme de tableau.
        $tasks = $queryBuilder->getResult();

        // Retourne le tableau des tâches, chaque tâche étant représentée par ses champs 'title' et 'description'.
        return $tasks;
    }

    //  supprime une tache par son id
    public function deleteOneTasks(int $id): bool
    {
        // Obtient le repository pour l'entité TaskEntity pour interagir directement avec la table de tâches.
        $repository = $this->em->getRepository(TaskEntity::class);

        // Recherche la tâche à supprimer par son ID.
        $task = $repository->findOneBy(['id' => $id]);

        // Supprime la tâche en utilisant l'EntityManager.
        $this->em->remove($task);
        $this->em->flush();

        return true; // Retourne true pour indiquer que la suppression a réussi.

    }

    // modifie une tache par son id
    public function updateOneTasks(int $id, TaskEntity $modifiedTask): void
    {
        // // Crée une requête DQL pour mettre à jour les champs title et description de la tâche spécifiée par l'ID.
        // $query = $this->em->createQuery(
        //     'UPDATE App\Entity\TaskEntity t
        // SET t.title = :newTitle, t.description = :newDescription
        // WHERE t.id = :id'
        // );

        // // Définit les paramètres de la requête.
        // $query->setParameter(':newTitle', $modifiedTask->getTitle());
        // $query->setParameter('newDescription', $modifiedTask->getDescription());
        // $query->setParameter('id', $id);

        // // Exécute la requête et récupère le nombre de lignes affectées.
        // $result = $query->execute();

        //------------------------ Methode Ilyass ------------------------//

        // Récupère la tâche actuelle depuis la base de données en recherchant par identifiant.
        $currentTask = $this->em->getRepository(TaskEntity::class)->findOneBy(['id' => $id]);

        // Met à jour le titre de la tâche actuelle en utilisant le titre de la tâche modifiée.
        $currentTask->setTitle($modifiedTask->getTitle());

        // Met à jour la description de la tâche actuelle en utilisant la description de la tâche modifiée.
        $currentTask->setDescription($modifiedTask->getDescription());

        // Applique les modifications en les enregistrant dans la base de données.
        $this->em->flush();

    }

}
