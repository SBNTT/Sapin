<?php

namespace App\Component;

final readonly class BasePage
{
} ?>

<template :uses="App\Component\Logo">
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
        <script src="https://cdn.tailwindcss.com"></script>

        <title>Sapin's todos</title>
    </head>

    <body>
    <main class="relative p-4 h-screen w-screen bg-gray-100 overflow-scroll bg-gradient-to-t from-[#c1dfc480] to-[#deecdd80]">
        <div class="m-auto max-w-[1200px]">
            <div class="flex items-center px-4">
                <Logo/>
                <h1 class="flex items-center text-3xl">
                    <span class="ml-3">
                        <slot :name="title">
                            Sapin's Todos
                        </slot>
                    </span>
                </h1>
                <span class="flex-1"></span>
                <slot :name="actions"></slot>
            </div>

            <div class="h-6"></div>

            <slot :name="content"></slot>
        </div>
    </main>
    </body>
    </html>
</template>
