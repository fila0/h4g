<?php

namespace Fila0\User;

use Symfony\Component\Security\Core\User\User as BaseUser;

class User extends BaseUser
{
    private $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}