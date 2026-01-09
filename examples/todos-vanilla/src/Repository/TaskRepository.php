<?php

namespace App\Repository;

use App\Model\Task;
use App\Model\TaskState;
use Exception;
use SQLite3;

final class TaskRepository
{
    private SQLite3 $database;

    public function __construct()
    {
        $this->database = new SQLite3(__DIR__ . '/../../todos.db');
    }

    /**
     * @return Task[]
     * @throws Exception
     */
    public function getAllTasks(): array
    {
        $result = $this->database->query("SELECT * FROM task")
            ?: throw new Exception('Fail to fetch all tasks');

        $tasks = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tasks[] = new Task(
                id: is_int($id = $row['id']) ? $id : throw new Exception('Invalid id'),
                title: is_string($title = $row['title']) ? $title : throw new Exception('Invalid title'),
                description: is_string($description = $row['description']) ? $description : throw new Exception('Invalid description'),
                state: TaskState::from(is_int($state = $row['state']) ? $state : throw new Exception('Invalid state')),
            );
        }

        return $tasks;
    }

    /**
     * @throws Exception
     */
    public function insertOne(
        string $title,
        string $description,
        TaskState $state,
    ): ?Task {
        $statement = $this->database->prepare(
            "INSERT INTO task (title, description, state) VALUES (:title, :description, :state)",
        ) ?: throw new Exception('Fail to initiate request');

        $statement->bindValue(':title', $title, SQLITE3_TEXT);
        $statement->bindValue(':description', $description, SQLITE3_TEXT);
        $statement->bindValue(':state', $state->value, SQLITE3_INTEGER);

        if ($statement->execute() === false) {
            return null;
        }

        return new Task(
            id: $this->database->lastInsertRowID(),
            title: $title,
            description: $description,
            state: $state,
        );
    }

    /**
     * @throws Exception
     */
    public function updateOneSState(int $id, TaskState $state): bool
    {
        $statement = $this->database->prepare(
            "UPDATE task SET state = :state WHERE id = :id"
        ) ?: throw new Exception('Fail to initiate request');

        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->bindValue(':state', $state->value, SQLITE3_INTEGER);

        return $statement->execute() !== false;
    }

    /**
     * @throws Exception
     */
    public function deleteOne(int $id): bool
    {
        $statement = $this->database->prepare(
            "DELETE FROM task WHERE id = :id"
        ) ?: throw new Exception('Fail to initiate request');

        $statement->bindValue(':id', $id, SQLITE3_INTEGER);

        return $statement->execute() !== false;
    }
}
