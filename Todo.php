<?php

namespace Entity;

use Components\NDatabase\NDatabase;
use Components\NSession\NSession;

class Todo
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    static public function createWithSessionUser(): Todo
    {
        return new self((int)NSession::get('user_id'));
    }

    public function getAllRecords(): array
    {
        return $res[] = NDatabase::getAllAssoc(
            "SELECT * 
            FROM `todo_list` 
            WHERE `user_id` = ? 
            ORDER BY `id` 
            DESC",
            [$this->userId]
        );
    }

    public function create(array $fields): void
    {
        NDatabase::query(
            "INSERT
            INTO `todo_list`
            SET `description` = ?,
                `status` = ?,
                `user_id` = ?",
            [
                $fields['description'],
                $fields['status'],
                $this->userId,
            ]
        );
    }

    public function getOneRecordId()
    {
        return NDatabase::getOne(
            "SELECT 
            LAST_INSERT_ID()
            FROM `todo_list`"
        );
    }

    public function delete(array $fields): void
    {
        foreach ($fields['ids'] as $id) {
            NDatabase::query(
                "DELETE
                    FROM `todo_list`
                    WHERE `id` = ?",
                [$id['id']]
            );
        }
    }

    public function statusEditOnCheckbox(array $fields): void
    {
        foreach ($fields['ids'] as $id) {
            NDatabase::query(
                "UPDATE `todo_list`
                    SET `status` = ?
                    WHERE `id` = ?",
                [
                    $fields['status'],
                    $id['id']
                ]
            );
        }
    }

    public function statusEditOnIcon(array $fields)
    {
        NDatabase::query(
            "UPDATE `todo_list`
                    SET `status` = ?
                    WHERE `id` = ?",
            [
                $fields['status'],
                $fields['id']
            ]
        );
    }
}
