<?php

namespace App\Model;

final class Task
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $description,
        public readonly TaskState $state,
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