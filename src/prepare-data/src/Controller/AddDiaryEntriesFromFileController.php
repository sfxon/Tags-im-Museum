<?php

namespace App\Controller;

use App\Services\DiaryPageNumberCheck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddDiaryEntriesFromFileController extends AbstractController
{
    private $currentPage = 0;
    
    #[Route('/addDiaryEntriesFromFile', name: 'app_add_diary_entries_from_file')]
    public function index(): Response
    {
        $this->currentPage = 0;
        $this->lastLineWasPageNumber = false;

        $file = new \SplFileObject("../../../data/tagebuecher-plain-text-separated/01.txt");

        while(!$file->eof()) 
        {
            $line =  $file->fgets();
            $line = trim($line);
            $pageNumber = DiaryPageNumberCheck::getPageNumberFromString($line);
            
            if(false !== $pageNumber) {
                $this->currentPage = $pageNumber;
            }

            var_dump($this->currentPage);
            die;

            break;
        }

        $file = null;

        die('test');


        /*return $this->render('add_diary_entries_from_file/index.html.twig', [
            'controller_name' => 'AddDiaryEntriesFromFileController',
        ]);*/
    }
}
