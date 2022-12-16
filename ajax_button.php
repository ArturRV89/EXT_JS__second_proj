<?php

namespace Entity;

use Components\NDatabase\NDatabase;
use Components\NSession\NSession;

class Todo
{
    private $description;
    private $userId = (int)NSession::get('user_id');
    private $status;
    private $idRecord;
    private array $idCase = [];
    public array $errors = [];

    /**
     * @param int $userIdSession
     * @return bool | array
     */
    public function getAllRecords()
    {
        $res = [];
        $errors = [];

        $res[] = NDatabase::getAllAssoc(
            "SELECT * 
            FROM `todo_list` 
            WHERE `user_id` = ? 
            ORDER BY `id` 
            DESC",
            [$this->userId]
        );

        if (empty($fields['ids'])) {
            throw new \InvalidArgumentException("ids is empty");
        }




        if (empty($res)) {
            return $errors[] = '';
        } else {
            return $res;
        }
    }

    /**
     * @param array $fields
     * @return bool | array
     */
    public function create(array $fields)
    {
        $this->description = !empty($fields['description']) ? htmlspecialchars(trim($fields['description'])) : $this->errors[] = 'description - failed';
        $this->status = $fields['status'] ?? $this->errors[] = 'status - failed';
        $this->userId = $fields['userId'] ?? $this->errors[] = 'userId - failed';

        if (empty($this->errors)) {
            NDatabase::query(
                "INSERT
                    INTO `todo_list`
                    SET `description` = ?,
                        `status` = ?,
                        `user_id` = ?",
                [
                    $this->description,
                    $this->status,
                    $this->userId,
                ]
            );
            return true;
        } else {
            return $this->errors;
        }
    }

    /**
     * @return array
     */
    public function oneRec()
    {
        return NDatabase::getOne(
            "SELECT
                    LAST_INSERT_ID()
                    FROM `todo_list`"
        );
    }

    public function delete(array $fields): void
    {
        if (empty($fields['ids'])) {
           throw new \InvalidArgumentException("ids is empty");
        }
        foreach ($fields['ids'] as $id) {
            NDatabase::query(
                "DELETE
                    FROM `todo_list`
                    WHERE `id` = ?",
                [$id['id']]
            );
        }
    }

    /**
     * @param array $fields
     * @return bool | array
     */
    public function statusEditOnCheckbox(array $fields)
    {
        $this->status = $fields['status'] ?? $this->errors[] = 'status - failed';
        $this->idCase['ids'] = is_array($fields['records']) ? $fields['records'] : $this->errors[] = 'ids - failed';

        if (empty($this->errors)) {
            foreach ($this->idCase['ids'] as $id) {
                NDatabase::query(
                    "UPDATE `todo_list`
                    SET `status` = ?
                    WHERE `id` = ?",
                    [
                        $this->status,
                        $id['id']
                    ]
                );
            }
            return true;
        } else {
            return $this->errors;
        }
    }

    public function statusEditOnIcon(array $fields)
    {
        $this->status = $fields['status'] ?? $this->errors[] = 'status - failed';
        $this->idRecord = $fields['idRecord'] ?? $this->errors[] = 'id - failed';

        if (empty($this->errors)) {
            NDatabase::query(
                "UPDATE `todo_list`
                    SET `status` = ?
                    WHERE `id` = ?",
                [
                    $this->status,
                    $this->idRecord
                ]
            );
            return true;
        } else {
            return $this->errors;
        }
    }
}


/////////////////////////////////////////////////////////////////////////////////////////


<?php

require_once '_session.php';

use Components\FormValidation\FormValidation;
use Components\FormValidation\RuleRequired;
use Entity\Todo;

$request = http_request('data');
$request = json_decode($request, true);
$action = $request['action'];

/** @throws Exception */
try {
    $todo = new Todo();
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
    $response['is_error'] = $exception->getMessage();
    $response['message'] = 'Something went wrong!';
}
response($response);













