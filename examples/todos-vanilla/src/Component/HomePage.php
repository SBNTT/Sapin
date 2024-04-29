<?php

namespace App\Component;

use App\Model\Task;

final readonly class HomePage
{
    /** @param Task[] $tasks */
    public function __construct(
        public array $tasks,
    ) {}
} ?>

<template :uses="
    App\Component\BasePage,
    App\Component\TasksList,
">
    <BasePage>
        <a
                href="/create-task"
                :slot="actions"
                class="py-2 px-4 rounded-lg bg-green-400 shadow hover:bg-green-300 transition"
        >
            Create a task
        </a>

        <TasksList
                :slot="content"
                :tasks="$this->tasks"
        />
    </BasePage>
</template>
