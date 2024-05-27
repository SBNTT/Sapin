<?php

use Sapin\Engine\Sapin;

Sapin::configure(
    cacheDirectory: __DIR__ . '/../var/cache/components',
);

$route = $_SERVER['REQUEST_METHOD'] . ' ' . strtok($_SERVER["REQUEST_URI"], '?');
require __DIR__ . '/Controller/' . match ($route) {
    'GET /' => 'HomePageController',
    'GET /create-task' => 'CreateTaskPageController',
    'POST /create-task' => 'CreateTaskController',
    'GET /update-task-state' => 'UpdateTaskStateController',
    'GET /delete-task' => 'DeleteTaskController',
    default => 'NotFoundPageController',
} . '.php';
