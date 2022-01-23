<?php

namespace App\Controller;

use App\Entity\ToDo;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/")
 */
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("user", name="user_index", methods={"GET"})
     */
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $decode = json_decode($request->getContent(), true);
        $data = [];
//        dd($decode);
        $founded = $userRepository->findOneBy(['email' => $decode['email']]);
        if ($founded) {
            if ($founded->jsonSerialize()['password'] !== UserController::hashPassword($decode['password'])) {
                return $this->response([
                    'status' => "405",
                    'message' => "Wrong password",
                ]);
            } else {
                return $this->response([
                    'status' => "200",
                    'message' => "Exist",
                ]);
            }
        } else {
            return $this->response([
                'status' => "402",
                'message' => "User not exist",
            ], "402");
        }
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("user", name="user_create", methods={"POST"})
     */
    public function create(Request $request, UserRepository $userRepository): Response
    {
        try {
            $decode = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response([
                    'status' => "400",
                    'message' => "Error during parsing json",
                ], "400");
            }

            $email = $decode['email'];
            $password = $decode['password'];

            if (!isset($email) || !isset($password)) {
                return $this->response([
                    'status' => "401",
                    'message' => "You should provide both login and password"
                ], "401");
            }

            $entityManager = $this->getDoctrine()->getManager();

            $founded = $userRepository->findOneBy(['email' => $email]);
            if (!$founded) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword($this->hashPassword($password));
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                return $this->response([
                    "status" => "402",
                    "message" => 'User with such login already registered',
                ], "402");
            }

            $data = [
                'status' => "200",
                'success' => "User added successfully ",
            ];
            return $this->response($data);
        } catch (Exception $e) {
            return $this->response([
                'status' => "403",
                'errors' => "Data no valid",
                'm' => $e->getMessage(),
            ], "403");
        }
    }

    public function response($data, $status = 200, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response([
                'status' => "400",
                'message' => "Error during parsing json",
            ], "400");
        }
        $request->request->replace($data);
        return $request;
    }

    public static function hashPassword($password)
    {
        return hash("sha256", $password);
    }
}
