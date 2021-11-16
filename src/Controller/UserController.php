<?php

namespace App\Controller;

use App\Entity\ToDo;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api")
 */
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("/user", name="user_create", methods={"POST"})
     */
    public function create(Request $request, UserRepository $userRepository): Response
    {
        try {
            $decode = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'status' => 400,
                    'message' => "Error during parsing json",
                ]);
            }

            $email = $decode['email'];
            $password = $decode['password'];

            if (!isset($email) || !isset($password)) {
                return $this->json([
                    'status' => 401,
                    'message' => "You should provide both login and password $email $password"
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();

            $founded = $userRepository->findOneBy(array('email' => $email));
            if (!$founded) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword($password);
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                return $this->json([
                    'status' => 402,
                    'message' => "User with such login already registered",
                ]);
            }

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

        } catch (Exception $e) {
            return $this->json([
                'status' => 403,
                'errors' => "Data no valid",
            ]);
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
            return $this->json([
                'status' => 400,
                'message' => "Error during parsing json",
            ]);
        }
        $request->request->replace($data);
        return $request;
    }

    public function hashPassword($password)
    {
        return hash("sha256", $password);
    }

}
