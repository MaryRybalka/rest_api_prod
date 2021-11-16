<?php

namespace App\Controller;

use App\Entity\ToDo;
use App\Form\ToDoType;
use App\Repository\ToDoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ToDoController
 * @package App\Controller
 * @Route("/api")
 */
class ToDoController extends AbstractController
{
    /**
     * @Route("/todo", name="todo_index", methods={"GET"})
     */
    public function index(Request $request, UserRepository $userRepository, ToDoRepository $toDoRepository): Response
    {
        try {
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('email') || !$request->get('password')) {
                throw new Exception('not valid', '105');
            }

            $decode = json_decode($request->getContent(), true);

            $user = $userRepository->findOneBy([
                "login" => $decode["login"],
                "password" => $decode["password"]
            ]);

            if ($user) {
                $todos = $toDoRepository->findBy([
                    "user" => $user
                ]);
                $result[] = "";

                foreach ($todos as $todo) {
                    $array = [
                        "id" => $todo->getId(),
                        "title" => $todo->getTitle(),
                        "description" => $todo->getDescription(),
                        "createDate" => $todo->getCreateDate(),
                        "author" => $todo->getAuthor()
                    ];

                    $result[] = $array;
                }

                $data = [
                    'status' => 200,
                    'todos' => $result
                ];
                return $this->response($data, 200);
            } else {
                $data = [
                    'status' => 400,
                    'message' => "User not found"
                ];
                return $this->response($data, 424);
            }
        } catch (Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }
    }

    /**
     * @Route("/todo", name="todo_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        try {
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('title') || !$request->request->get('author')) {
                throw new Exception();
            }

            $todo = new ToDo();
            $todo->setTitle($request->get('title'));
            $todo->setDescription($request->get('description'));
            $todo->setAuthor($request->get('author'));
            $entityManager->persist($todo);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

        } catch (Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }
//        $toDo = new ToDo();
//        $form = $this->createForm(ToDoType::class, $toDo);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($toDo);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('to_do_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('to_do/new.html.twig', [
//            'to_do' => $toDo,
//            'form' => $form,
//        ]);
    }

    /**
     * @Route("/todo/{id}", name="todo_update", methods={"PUT"})
     */
    public function edit(Request $request, ToDoRepository $todoRepository, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        try {
            $todo = $todoRepository->find($id);

            if (!$todo) {
                $data = [
                    'status' => 404,
                    'errors' => "Post not found",
                ];
                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('title') || !$request->request->get('author')) {
                throw new \Exception();
            }

            $todo->setTitle($request->get('title'));
            $todo->setDescription($request->get('description'));
            $todo->setAuthor($request->get('author'));
            $todo->setCreateDate(\DateTimeInterface::COOKIE);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "Post updated successfully",
            ];
            return $this->response($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

//        $form = $this->createForm(ToDoType::class, $toDo);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->getDoctrine()->getManager()->flush();
//
//            return $this->redirectToRoute('to_do_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('to_do/edit.html.twig', [
//            'to_do' => $toDo,
//            'form' => $form,
//        ]);
    }

    /**
     * @Route("/todo/{id}", name="todo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ToDo $toDo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $toDo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($toDo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('todo_index', [], Response::HTTP_SEE_OTHER);
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
}
