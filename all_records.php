<?php

require_once '_session.php';

use Components\NDatabase\NDatabase;
use Components\NSession\NSession;
use Entity\Todo;

$todo = Todo::createWithSessionUser();

function getAllRecords(object $todo): array
{
    try {
        $queryResponse = $todo->getAllRecords();
        $response['success'] = true;
        $response['is_error'] = false;
        $response['records'] = $queryResponse[0];
    } catch (\Throwable $exception) {
        $response['is_error'] = true;
        $response['message'] = 'Something went wrong!' . $exception->getMessage();
    }
    return $response;
}

response(getAllRecords($todo));
