<?php

namespace App\Controller;

use App\Component\ErrorPage;
use App\Model\TaskState;
use App\Repository\TaskRepository;
use Sapin\Sapin;

$title = htmlspecialchars($_POST['title'] ?? '');
$description = htmlspecialchars($_POST['description'] ?? '');

$taskRepository = new TaskRepository();
$task = $taskRepository->insertOne($title, $description, TaskState::PENDING);

if ($task === null) {
    http_response_code(500);
    Sapin::compileAndRender(ErrorPage::class, fn() => new ErrorPage(
        message: 'Error 500: Internal server error'
    ));
    exit();
}

header('Location: /');
