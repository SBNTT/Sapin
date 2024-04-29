<?php

namespace App\Controller;

use App\Component\ErrorPage;
use App\Model\TaskState;
use App\Repository\TaskRepository;
use Sapin\Sapin;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$stateValue = filter_input(INPUT_GET, 'state', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

if (!is_int($id) || !is_int($stateValue) || ($state = TaskState::tryFrom($stateValue)) === null) {
    http_response_code(400);
    Sapin::compileAndRender(ErrorPage::class, fn() => new ErrorPage(
        message: 'Error 400: Bad request'
    ));
    exit();
}

$taskRepository = new TaskRepository();

if (!$taskRepository->updateOneSState($id, $state)) {
    http_response_code(500);
    Sapin::compileAndRender(ErrorPage::class, fn() => new ErrorPage(
        message: 'Error 500: Internal server error'
    ));
    exit();
}

header('Location: /');
