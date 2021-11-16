<?php

namespace App\Entity;

class Token
{
    public function createToken($payload)
    {
        $Sql = "INSERT INTO db_token (user_id, jwt_token) VALUES (:user_id, :jwt_token)";
        Parent::query($Sql);
        Parent::bindParams('user_id', $payload['user_id']);
        Parent::bindParams('jwt_token', $payload['jwt_token']);

        $Token = Parent::execute();
        if ($Token) {
            return array(
                'status' => true,
                'data' => $payload
            );
        }

        return array(
            'status' => false,
            'data' => []
        );
    }

    /**
     * fetchToken
     *
     * fetches an existing Token using the $token
     *
     * @param string $token     The token that will be used in matching the closest token from the database.
     * @return array Anonymous
     */
    public function fetchToken($token)
    {
        $Sql = "SELECT * FROM `db_token` WHERE jwt_token = :jwt_token";
        Parent::query($Sql);
        Parent::bindParams('jwt_token', $token);

        $Data = Parent::fetch();
        if (!empty($Data)) {
            return array(
                'status' => true,
                'data' => $Data
            );
        }

        return array(
            'status' => false,
            'data' => []
        );
    }
}