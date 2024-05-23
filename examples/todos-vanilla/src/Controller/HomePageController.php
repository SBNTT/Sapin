<?php

namespace App\Controller;

use App\Component\HomePage;
use App\Repository\TaskRepository;
use Sapin\Sapin;

$taskRepository = new TaskRepository();
$tasks = $taskRepository->getAllTasks();

Sapin::render(new HomePage($tasks));