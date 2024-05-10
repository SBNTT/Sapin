<?php

namespace App\Component;

use App\Model\Task;
use App\Model\TaskState;

final readonly class TaskListItem
{
    public int $inProgressState;
    public int $doneState;

    public function __construct(
        public Task $task,
    )
    {
        $this->inProgressState = TaskState::IN_PROGRESS->value;
        $this->doneState = TaskState::DONE->value;
    }
} ?>

<template>
    <li
            id="task-{{ $this->task->id }}-list-item"
            class="group flex items-center p-4 bg-gray-50 hover:bg-green-50 border rounded-xl hover:shadow-lg transition"
    >
        <div class="flex-1">
            <p>
                {{ $this->task->title }}
            </p>
            <p class="font-light italic text-gray-500">
                {{ $this->task->description }}
            </p>
        </div>

        <div class="flex items-center gap-3 mr-2 opacity-0 group-hover:opacity-100 transition">
            <a
                    :if="$this->task->isPending()"
                    href="/update-task-state?id={{ $this->task->id }}&state={{ $this->inProgressState }}"
                    class="py-2 px-4 rounded-full bg-blue-50 text-blue-400 border border-blue-300 border-opacity-0 hover:border-opacity-100 transition"
            >
                Start
            </a>

            <a
                    :else-if="$this->task->isInProgress()"
                    href="/update-task-state?id={{ $this->task->id }}&state={{ $this->doneState }}"
                    class="py-2 px-4 rounded-full bg-green-50 text-green-500 border border-green-400 border-opacity-0 hover:border-opacity-100 transition"
            >
                Done
            </a>

            <a
                    href="/delete-task?id={{ $this->task->id }}"
                    class="material-symbols-outlined p-2 rounded-full bg-red-50 text-red-400 border border-red-300 border-opacity-0 hover:border-opacity-100 transition"
            >
                delete
            </a>
        </div>
    </li>
</template>
