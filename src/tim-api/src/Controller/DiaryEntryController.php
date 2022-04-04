<?php

namespace App\Controller;

use App\Services\DiaryEntryService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DiaryEntryController extends AbstractController
{
    private $doctrine;
    private $request;
    
    #[Route('/api/v0.1/diary/{diaryId}/entries', name: 'app_diary_entries')]
    public function index(
        int $diaryId,
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
            $limit = 500;
        }

        // Load a list of authors
        $diaryEntryService = new DiaryEntryService($this->doctrine);
        $diaryEntries = $diaryEntryService->loadDiaryEntriesArray($diaryId, $index, $limit);

        return new JsonResponse($diaryEntries);
    }

    #[Route('/api/v0.1/diaries/entries', name: 'app_diary_entries')]
    public function searchEntries(
        ManagerRegistry $doctrine,
        Request $request): JsonResponse
    {
        $this->doctrine = $doctrine;
        $this->request = $request;

        $index = (int)$this->request->query->get('index');
        $limit = (int)$this->request->query->get('limit');
        $keyword = $this->request->query->get('keyword');

        if($index < 0) {
            $limit = 0;
        }

        if($limit < 1 || $limit > 50) {
            $limit = 500;
        }

        // Load a list of authors
        $diaryEntryService = new DiaryEntryService($this->doctrine);
        $diaryEntries = $diaryEntryService->searchEntries($keyword, $index, $limit);

        return new JsonResponse($diaryEntries);
    }
}
