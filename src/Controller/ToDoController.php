<?php

namespace App\Controller;

use Exception;
use App\Entity\ToDo;
use Firebase\JWT\JWT;
use App\Repository\ToDoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ToDoController
 * @package App\Controller
 * @Route("/")
 */
class ToDoController extends AbstractController
{
    private $author;

    /**
     * @param Request $request
     * @param ToDoRepository $todoRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("todo", name="todo_index", methods={"GET"})
     */
    public function index(Request $request, ToDoRepository $todoRepository, UserRepository $userRepository): Response
    {
//        $jwtauth = $this->login($request, $userRepository);
//        $decode = json_decode($jwtauth->getContent(), true);
        $data = [];
//        $founded = $userRepository->findOneBy(array('email' => $decode['email']));

        $decode = json_decode($request->getContent(), true);
        $data = [];
        $founded = $userRepository->findOneBy(array('email' => $decode['email']));
        if ($founded) {
            if ($founded->jsonSerialize()['password'] !== UserController::hashPassword($decode['password'])) {
                return $this->response([
                    'status' => "405",
                    'message' => "Wrong password",
                ], "405");
            } else {
                $this->author = $founded->jsonSerialize()['id'];
                $data = $todoRepository->findBy(array('author' => $this->author));
                for ($i = 0; $i < count($data); $i++) $data[$i] = $data[$i]->jsonSerialize();
                return $this->response($data);
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
     * @param ToDoRepository $todoRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @Route("todo", name="todo_add", methods={"POST"})
     * @throws Exception
     */
    public function new(Request $request, ToDoRepository $todoRepository, UserRepository $userRepository): Response
    {
        $decode = json_decode($request->getContent(), true);
        $data = [];
        $founded = $userRepository->findOneBy(array('email' => $decode['email']));
        if ($founded) {
            if ($founded->jsonSerialize()['password'] !== UserController::hashPassword($decode['password'])) {
                return $this->response([
                    'status' => "405",
                    'message' => "Wrong password",
                ], "405");
            } else {
                $this->author = $founded;
                $description = $decode['description'];
                $title = $decode['title'];

                if (!isset($title) || !isset($description)) {
                    return $this->response([
                        'status' => "401",
                        'message' => "You should provide title and description "
                    ], "401");
                }

                $entityManager = $this->getDoctrine()->getManager();

                $todo = new ToDo();
                $todo->setTitle($title);
                $todo->setDescription($description);
                $todo->setAuthor($this->author);
                $todo->setCreateDate(new \DateTime('now', new \DateTimeZone('Africa/Casablanca')));
                $entityManager->persist($todo);
                $entityManager->flush();

                $data = [
                    'status' => "200",
                    'success' => "ToDo added successfully",
                ];
                return $this->response($data);
            }
        } else {
            return $this->response([
                'status' => "402",
                'message' => "User not exist",
            ], "402");
        }
    }

    /**
     * @Route("todo/{id}", name="todo_update", methods={"PUT"})
     */
    public function edit(Request $request, ToDoRepository $todoRepository, $id, UserRepository $userRepository): Response
    {
        $decode = json_decode($request->getContent(), true);
        $data = [];
        $founded = $userRepository->findOneBy(array('email' => $decode['email']));
        if ($founded) {
            if ($founded->jsonSerialize()['password'] !== UserController::hashPassword($decode['password'])) {
                return $this->response([
                    'status' => "405",
                    'message' => "Wrong password",
                ], "405");
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $todo = $todoRepository->find($id);

                if (!$todo) {
                    return $this->response([
                        'status' => "407",
                        'errors' => "Todo not found",
                    ], "407");
                }
                $title = "";
                $description = "";
                if (isset($decode['title'])) $title = $decode['title'];
                if (isset($decode['description'])) $description = $decode['description'];

                if ($title) $todo->setTitle($title);
                if ($description) $todo->setDescription($description);
                $todo->setCreateDate(new \DateTime('now', new \DateTimeZone('Africa/Casablanca')));
                $entityManager->flush();

                $data = [
                    'status' => "200",
                    'errors' => "ToDo was updated successfully",
                ];
                return $this->response($data);
            }
        } else {
            return $this->response([
                'status' => "402",
                'message' => "User not exist",
            ], "402");
        }
    }

    /**
     * @Route("todo/{id}", name="todo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ToDoRepository $todoRepository, $id, UserRepository $userRepository): Response
    {
        $decode = json_decode($request->getContent(), true);
        $data = [];
        $founded = $userRepository->findOneBy(array('email' => $decode['email']));
        if ($founded) {
            if ($founded->jsonSerialize()['password'] !== UserController::hashPassword($decode['password'])) {
                return $this->response([
                    'status' => "405",
                    'message' => "Wrong password",
                ], "405");
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $todo = $todoRepository->find($id);

                if (!$todo) {
                    $data = [
                        'status' => "407",
                        'errors' => "Todo not found",
                    ];
                    return $this->response($data, "407");
                }
                $entityManager->remove($todo);
                $entityManager->flush();

                $data = [
                    'status' => "200",
                    'errors' => "ToDo was deleted successfully",
                ];
                return $this->response($data);
            }
        } else {
            return $this->response([
                'status' => "402",
                'message' => "User not exist",
            ],"402");
        }
    }

    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, $status = 200, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

    public function login(Request $request, UserRepository $userRepository)
    {
        try {
            $user_name = $request->get('email', '');
            $password = $request->get('password', '');
            $user = $userRepository->findOneBy(['email' => $user_name, 'password' => $password]);
            if (!$user) {
                return $this->response([
                    'status' => "400",
                    'message' => "Incorrect email or password",
                ]);
            }
            unset($user['password']);
            // логин успешной авторизации
            $token = $this->getJWTToken($user);
            cache('user-' . $user['email'], $user);
            return $this->response(['token' => $token]);
        } catch (Exception $e) {
            return $this->response([
                'status' => "403",
                'errors' => $e->getMessage(),
            ], "403");
        }
    }

    public function getJWTToken($value)
    {
        $time = time();
        $payload = [
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + 7200,
            'data' => [
                'email' => $value['email']
            ]
        ];
        $key = env('JWT_SECRET_KEY');
        $alg = 'HS256';
        return JWT::encode($payload, $key, $alg);
    }

    public function checkLogged(Request $request, UserRepository $userRepository): Response
    {
        $decode = json_decode($request->getContent(), true);
        $founded = $userRepository->findOneBy(array('email' => $decode['email']));
        if (!$founded) {
            return $this->response([
                'status' => "402",
                'message' => "User not exist",
            ], "402");
        } else {
            return $founded->jsonSerialize();
        }
    }
}
