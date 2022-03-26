<?php

namespace App\Controller;

use App\Enum\LineType;
use App\DTO\DiaryEntry;
use App\DTO\DiaryHeading;
use App\Services\DiaryPageNumberCheck;
use App\Services\LineProcessing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddDiaryEntriesFromFileController extends AbstractController
{
    private $currentPage = 0;
    private $lastLineType = null;
    private $diaryHeading = null;
    private $diaryHeadingComplete = false;
    
    #[Route('/addDiaryEntriesFromFile', name: 'app_add_diary_entries_from_file')]
    public function index(): Response
    {
        $this->currentPage = 0;
        $this->lastLineType = null;
        $this->diaryHeading = new DiaryHeading();
        $this->diaryHeadingComplete = false;

        $file = new \SplFileObject("../../../data/tagebuecher-plain-text-separated/01.txt");

        while(!$file->eof()) 
        {
            $line =  $file->fgets();
            $line = trim($line);
            
            // Prüfe Zeilen auf unterschiedliche Typen
            $pageNumber = DiaryPageNumberCheck::getPageNumberFromString($line);
            
            if(false !== $pageNumber) {
                $this->currentPage = $pageNumber;
                $this->lastLineType = LineType::PageNumber;
                continue;
            }

            // Weitere Verarbeitung
            $line = LineProcessing::getContent($line);

            // Falls das Diary Heading noch nicht existiert, prüfen wir darauf, weil der Text immer damit beginnt!
            if(false === $this->diaryHeadingComplete) {
                if(strlen($line) > 0) {
                    if($this->diaryHeading->getNumber() ===  null) {
                        $this->diaryHeading->setNumber($line);
                        $this->lastLineType = LineType::DiaryHeading_DiaryNumber;
                        continue;
                    }

                    if($this->diaryHeading->getTitle() ===  null) {
                        $this->diaryHeading->setTitle($line);
                        $this->lastLineType = LineType::DiaryHeading_DiaryTitle;
                        continue;
                    }

                    if($this->diaryHeading->getFromTo() ===  null) {
                        $this->diaryHeading->setFromTo($line);
                        $this->lastLineType = LineType::DiaryHeading_DiaryFromTo;
                        continue;
                    }

                    if($this->diaryHeading->getAuthors() ===  null) {
                        $this->diaryHeading->setAuthors($line);
                        $this->lastLineType = LineType::DiaryHeading_Authors;
                        $this->diearyHeadingComplete = true;
                        continue;
                    }
                }
            }
        }

        var_dump($this->diaryHeading);

        $file = null;

        die('test');


        /*return $this->render('add_diary_entries_from_file/index.html.twig', [
            'controller_name' => 'AddDiaryEntriesFromFileController',
        ]);*/
    }
}
