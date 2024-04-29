<?php

namespace App\Model;

final readonly class Task
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public TaskState $state,
    )
    {
    }

    public function isPending(): bool
    {
        return $this->state === TaskState::PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->state === TaskState::IN_PROGRESS;
    }

    public function isDone(): bool
    {
        return $this->state === TaskState::DONE;
    }
}