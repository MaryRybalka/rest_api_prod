<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\HealthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HealthController
 * @package App\Controller
 * @Route("/")
 */
class HealthController extends AbstractController
{
    /**
     * @param HealthService $healthService
     * @return JsonResponse
     * @Route("health", name="health", methods={"GET"})
     */
    public function index(HealthService $healthService): Response
    {
        return $this->response([
            'status' => "200",
            'APP_ENV' => $healthService->getEnvName(),
        ], "200");
    }

    public function response($data, $status = 200, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}