<?php

namespace App\Controller;

use App\Services\DiaryService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DiaryController extends AbstractController
{
    #[Route('/api/v0.1/diaries', name: 'app_diaries')]
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
        $diaryService = new DiaryService($this->doctrine);
        $diaries = $diaryService->loadDiariesArray($index, $limit);

        return new JsonResponse($diaries);
    }
}
