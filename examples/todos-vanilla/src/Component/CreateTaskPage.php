<?php

namespace App\Component;

final readonly class CreateTaskPage
{
} ?>

<template :uses="
    App\Component\BasePage,
">
    <BasePage>
        <fragment :slot="title">
            Create a task
        </fragment>

        <button
                :slot="actions"
                type="submit"
                form="create-task-form"
                class="p-2 rounded-full bg-green-400 shadow hover:bg-green-300 transition"
        >
            <span class="material-symbols-outlined">done</span>
        </button>

        <form
                :slot="content"
                id="create-task-form"
                method="post"
                action="/create-task"
                class="flex flex-col gap-6"
        >
            <div>
                <label
                        for="title-input"
                        class="block px-4 mb-2 text-sm font-medium text-gray-900"
                >
                    Title
                </label>
                <input
                        id="title-input"
                        name="title"
                        type="text"
                        required
                        class="p-4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:bg-green-50 focus:ring-green-500 focus:border-green-500 block w-full outline-none"
                />
            </div>

            <div>
                <label
                        for="description-textarea"
                        class="block px-4 mb-2 text-sm font-medium text-gray-900"
                >
                    Description
                </label>
                <textarea
                        id="description-textarea"
                        name="description"
                        rows="6"
                        class="block p-4 w-full text-sm text-gray-900 bg-gray-50 rounded-lg focus:bg-green-50 border border-gray-300 focus:ring-green-500 focus:border-green-500 outline-none"
                ></textarea>
            </div>
        </form>
    </BasePage>
</template>
