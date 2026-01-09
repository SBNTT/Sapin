<?php

namespace App\Controller;

use App\Component\ErrorPage;
use App\Model\TaskState;
use App\Repository\TaskRepository;
use Sapin\Engine\Sapin;

$title = trim(is_string($t = filter_input(INPUT_POST, 'title')) ? $t : '');
$description = trim(is_string($d = filter_input(INPUT_POST, 'description')) ? $d : '');

$taskRepository = new TaskRepository();
$task = $taskRepository->insertOne($title, $description, TaskState::PENDING);

if ($task === null) {
    http_response_code(500);
    Sapin::render(new ErrorPage(
        message: 'Error 500: Internal server error'
    ));
    exit();
}

header('Location: /');
