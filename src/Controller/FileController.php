<?php

namespace App\Controller;

use Exception;
use App\Entity\File;
use Firebase\JWT\JWT;
use App\Service\FileUploader;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class FileController
 * @package App\Controller
 * @Route("/")
 */
class FileController extends AbstractController
{
    /**
     * @param FileRepository $fileRepository
     * @return JsonResponse
     * @Route("files/", name="file_index", methods={"GET"})
     */
    public function index(FileRepository $fileRepository): Response
    {
        $data = $fileRepository->findAll();
        if (count($data)>0) {
            for ($i = 0; $i < count($data); $i++) $data[$i] = $data[$i]->jsonSerialize();
            return $this->response($data);
        } else {
            return $this->response([
                'status' => "201",
                'message' => "No Files",
            ], "201");
        }
    }

    /**
     * @param Request $request
     * @param FileRepository $fileRepository
     * @param FileUploader $fileUploader
     * @param $name
     * @return JsonResponse
     * @Route("files/{name}", name="file_add", methods={"POST"})
     */
    public function new(Request $request, FileRepository $fileRepository, FileUploader $fileUploader, $name): Response
    {
        $fileData = $request->files->get('file');
        if ($fileData) {
            try {
                $sluggedFileName = $fileUploader->upload($fileData);
            } catch (Exception $error) {
                return $this->response([
                    'status' => "403",
                    'message' => $error->getMessage(),
                ], "403");
            }


            $entityManager = $this->getDoctrine()->getManager();
            $file = new File();
            $file->setSafeName($sluggedFileName);
            $file->setName($name);
            $file->setType($this->getParameter('files_directory'));

            $entityManager->persist($file);
            $entityManager->flush();

            return $this->response([
                'status' => "200",
                'message' => "File was added successfully",
            ]);
        }
        return $this->response([
            'status' => "402",
            'message' => "Incorrect data",
        ], "402");
    }

    /**
     * @param FileRepository $fileRepository
     * @return BinaryFileResponse
     * @Route("files/{id}", name="file_by_id", methods={"GET"})
     */
    public function getById(FileRepository $fileRepository, $id): Response
    {
        $data = $fileRepository->find($id);
        if ($data) {
            return $this->binaryResponse($data->getMime());
        } else {
            return $this->response([
                'status' => "401",
                'message' => "No File with that id: " . $id,
            ], "401");
        }
    }

    /**
     * @param Request $request
     * @param FileRepository $fileRepository
     * @return JsonResponse
     * @Route("files/{id}", name="file_delete", methods={"DELETE"})
     */
    public function delete(Request $request, FileRepository $fileRepository, $id): Response
    {
        $file = $fileRepository->find($id);
        if (!$file) {
            return $this->response([
                'status' => "401",
                'message' => "No File with that id: " . $id,
            ]);
        } else {
            $filesystem = new Filesystem();
            try {
                $filesystem->remove([$file->getMime()]);
            } catch (IOExceptionInterface $exception) {
                return $this->response([
                    'status' => "405",
                    'message' => "Can't delete from directory",
                ], "405");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($file);
            $entityManager->flush();

            return $this->response([
                'status' => "200",
                'message' => "File was deleted successfully",
            ], "200");
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

    /**
     * Returns a binary response
     *
     * @param $mime
     * @return BinaryFileResponse
     */
    public function binaryResponse($mime): BinaryFileResponse
    {
        return new BinaryFileResponse($mime);
    }
}
