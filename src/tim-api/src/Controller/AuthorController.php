<?php

namespace App\Controller;

use App\Services\AuthorService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    private $doctrine;
    private $request;
    
    #[Route('/api/v0.1/authors', name: 'api_authors')]
    public function index(
        ManagerRegistry $doctrine,
        Request $request): JsonResponse
    {
        $this->doctrine = $doctrine;
        $this->request = $request;

        $index = (int)$this->request->query->get('index');
        $limit = (int)$this->request->query->get('limit');

        if($index < 0) {
            $limit = 0;
        }

        if($limit < 1 || $limit > 50) {
            $limit = 50;
        }

        // Load a list of authors
        $authorService = new AuthorService($this->doctrine);
        $authors = $authorService->loadAuthorsArray($index, $limit);

        return new JsonResponse($authors);
    }
}
