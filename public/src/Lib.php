<?php

declare(strict_types=1);

namespace Renakdup\Src;

function validateUserForm(array $fields): array
{
    $errors = [];

    if (! isset($fields['nickname']) || $fields['nickname'] === '') {
        $errors['nickname'] = 'Nickname is empty';
    }

    if (! isset($fields['email']) || $fields['email'] === '') {
        $errors['email'] = 'Email is empty';
    }

    if (! isset($fields['id']) || $fields['id'] === '') {
        $errors['id'] = 'Id is empty';
    }

    return $errors;
}

function getUsers(): array
{
    $data = file_get_contents(ABSPATH . '/database/user.json');

    if (! $data) {
        return [];
    }

    return json_decode($data, true);
}

function saveUser(array $user): bool
{
    $users = getUsers();
    $users[] = $user;
    $usersEncode = json_encode($users);

    return file_put_contents(ABSPATH . '/database/user.json', $usersEncode);
}

function searchUsers(string $searchQuery, string $field): array
{
    $users = getUsers();

    return collect($users)
        ->reject(function ($val) use ($field, $searchQuery) {
            return strpos($val[$field], $searchQuery) === false;
        })
        ->toArray();
}