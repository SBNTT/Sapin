<?php

namespace App\Repository;

use App\Model\Task;
use App\Model\TaskState;
use SQLite3;

final class TaskRepository
{
    private SQLite3 $database;

    public function __construct()
    {
        $this->database = new SQLite3(__DIR__ . '/../../todos.db');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAllTasks(): array
    {
        $result = $this->database->query("SELECT * FROM task")
            ?: throw new \Exception('Fail to fetch all tasks');

        $tasks = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tasks[] = new Task(
                id: $row['id'],
                title: $row['title'],
                description: $row['description'],
                state: TaskState::from($row['state']),
            );
        }

        return $tasks;
    }

    public function insertOne(
        string $title,
        string $description,
        TaskState $state,
    ): ?Task
    {
        $statement = $this->database->prepare(
            "INSERT INTO task (title, description, state) VALUES (:title, :description, :state)",
        );

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

    public function updateOneSState(int $id, TaskState $state): bool
    {
        $statement = $this->database->prepare("UPDATE task SET state = :state WHERE id = :id");
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->bindValue(':state', $state->value, SQLITE3_INTEGER);

        return $statement->execute() !== false;
    }

    public function deleteOne(int $id): bool
    {
        $statement = $this->database->prepare("DELETE FROM task WHERE id = :id");
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);

        return $statement->execute() !== false;
    }
}