<?php

namespace App\Controller;

use App\Component\ErrorPage;
use App\Repository\TaskRepository;
use Sapin\Sapin;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

if (!is_int($id)) {
    http_response_code(400);
    Sapin::render(new ErrorPage(
        message: 'Error 400: Bad request'
    ));
    exit();
}

$taskRepository = new TaskRepository();

if (!$taskRepository->deleteOne($id)) {
    http_response_code(500);
    Sapin::render(new ErrorPage(
        message: 'Error 500: Internal server error'
    ));
    exit();
}

header('Location: /');
