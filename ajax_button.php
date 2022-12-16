<?php

require_once '_session.php';

use Components\FormValidation\FormValidation;
use Components\FormValidation\RuleRequired;
use Components\NSession\NSession;
use Entity\Todo;

$request = http_request('data');
$request = json_decode($request, true);
$action = $request['action'];

/**
 * @throws Exception
 */
try {
    $todo = Todo::createWithSessionUser();

    switch ($action) {
        case 'create':
            $fv = new FormValidation();

            $fv->addRule(new RuleRequired('description', 'Поле обязательно для заполнения'));

            $request['description'] = trim($request['description']);
            $request['description'] = htmlspecialchars($request['description']);

            $fields = [
                'description' => $request['description'],
                'status' => $request['status'],
                'userId' => $request['user_id'],
            ];

            if (!$fv->validate($fields)) {
                $errorsForm = $fv->getErrors();
                $response['is_error'] = true;
                $response['messages'] = $errorsForm['msg'];
            } else {
                $todo->create($fields);
                $response['success'] = true;
                $response['recordId'] = $todo->getOneRecordId();
                $response['message'] = 'Created case';
            }

            break;

        case 'delete':

            $fields = [
                'ids' => $request['ids']
            ];

            $todo->delete($fields);
            $response['success'] = true;
            $response['message'] = 'Case(s) deleted!';

            break;

        case 'statusEditOnCheckbox':

            $fields = [
                'status' => $request['status'],
                'ids' => $request['ids']
            ];

            $todo->statusEditOnCheckbox($fields);
            $response['success'] = true;
            $response['message'] = 'Status edit!';

            break;

        case 'statusEditOnIcon':

            $fields = [
                'status' => $request['status'],
                'id' => $request['id']
            ];

            $todo->statusEditOnIcon($fields);
            $response['success'] = true;
            $response['message'] = 'Status edit!';

            break;
        default:
            throw new \Exception("Case is unknown: '$action'");
    }
} catch (\Throwable $exception) {
    $response['success'] = false;
    $response['errors'] = true;
    $response['is_error'] = true;
    $response['message'] = 'Something went wrong! ' . $exception->getMessage();
}
response($response);

