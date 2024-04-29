<?php

namespace App\Component;

final readonly class ErrorPage
{
    public function __construct(
        public string $message,
    )
    {
    }
} ?>

<template :uses="
    App\Component\BasePage,
">
    <BasePage>
        <fragment :slot="title">
            Oh noo...
        </fragment>

        <h1 :slot="content" class="text-3xl px-4">
            {{ $this->message }}
        </h1>
    </BasePage>
</template>
