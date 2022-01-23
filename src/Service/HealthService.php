<?php

namespace App\Service;

class HealthService
{
    private $AppEnvName;

    public function __construct($health)
    {
        $this->AppEnvName = $health;
    }

    public function getEnvName()
    {
        return $this->AppEnvName;
    }
}