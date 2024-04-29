<?php

use Sapin\Sapin;

Sapin::configure(
    cacheDirectory: __DIR__ . '/../var/cache/components',
);

require __DIR__ . '/Controller/' . match ($_SERVER['REQUEST_METHOD'] . ' ' . strtok($_SERVER["REQUEST_URI"], '?')) {
    'GET /' => 'HomePageController',
    'GET /create-task' => 'CreateTaskPageController',
    'POST /create-task' => 'CreateTaskController',
    'GET /update-task-state' => 'UpdateTaskStateController',
    'GET /delete-task' => 'DeleteTaskController',
    default => 'NotFoundPageController',
} . '.php';
