<?php

namespace App\Controller;

use App\Enum\LineType;
use App\DTO\DiaryEntry;
use App\DTO\DiaryHeading;
use App\Services\DateHelper;
use App\Services\AuthorService;
use App\Services\DiaryService;
use App\Services\DiaryEntryService;
use App\Services\DiaryEntryLinesService;
use App\Services\DiaryPageNumberCheck;
use App\Services\LineProcessing;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddDiaryEntriesFromFileController extends AbstractController
{
    private $currentPage = 0;
    private $lastLineType = null;
    private $diaryHeading = null;
    private $diaryHeadingComplete = false;
    private $diarySection = null;
    private $authorIds = [];
    private $diaryId;
    private $doctrine;
    
    #[Route('/addDiaryEntriesFromFile', name: 'app_add_diary_entries_from_file')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->doctrine = $doctrine;
        $this->currentPage = 0;
        $this->lastLineType = null;
        $this->diaryHeading = new DiaryHeading();
        $this->diaryHeadingComplete = false;
        $endEntry = false;
        $lines = [];

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
                $this->parseDiaryHeading($line);
                continue;
            }

            // Textdaten laden
            if(true === $this->isSection($line)) {
                $endEntry = true;
            } else if(true === $this->isEntryHeading($line)) {
                $endEntry = true;
            } else {
                $lines[] = $line;
            }

            if($endEntry && count($lines) > 0) {
                var_dump($lines);
                echo '<hr />';
                $lines = [];
            }

            $endEntry = false;
        }

        //var_dump($this->diaryHeading);

        $file = null;


        /*return $this->render('add_diary_entries_from_file/index.html.twig', [
            'controller_name' => 'AddDiaryEntriesFromFileController',
        ]);*/

        die('***');
    }

    private function isSection($line) {
        $parts = explode(" ", $line);
        
        if(count($parts) == 2) {
            $month = $parts[0];
            $year = $parts[1];

            $month = DateHelper::getMonthByName($month);

            if(is_int($month) && is_numeric($month) && $month > 0 && $month < 13) {
                if(is_numeric($year)) {
                    //$this->diarySection = $line;
                    return true;
                }
            }
        }

        return false;
    }

    // For example: Donnerstag, den 1. 9. 1932.
    private function isEntryHeading($line) {
        // Explode on Komma
        $parts = explode(',', $line);

        // zwei Teile?
        if(!is_array($parts) || count($parts) != 2) {
            return false;
        }

        // im ersten Teil: Wochentag parsen
        if(strlen($parts[0]) > 10) {        // Längster String ist "Donnerstag", mit 10 Buchstaben.
            return false;
        }

        $weekday = DateHelper::getWeekdayByName($parts[0]);

        if(false === $weekday) {
            return false;
        }

        // im zweiten Teil: den enthalten?


        if(strpos($parts[1], " den ") === false) {
            return false;
        }

        $dateString = str_replace(" den ", "", $parts[1]);

        // im zweiten Teil: endet auf einen Punkt?
        if(strpos(strrev($dateString), ".") != 0) {
            return false;
        }

        // den . entfernen
        $dateString = rtrim($dateString, '.');
        
        // im zweiten Teil das Datum parsen
        //DateHelper::parseNumericTextualDate($dateString);
        
        return true;
    }

    private function parseDiaryHeading($line) {
        if(strlen($line) > 0) {
            if($this->diaryHeading->getNumber() ===  null) {
                $this->diaryHeading->setNumber($line);
                $this->lastLineType = LineType::DiaryHeading_DiaryNumber;
                return true;
            }

            if($this->diaryHeading->getTitle() ===  null) {
                $this->diaryHeading->setTitle($line);
                $this->lastLineType = LineType::DiaryHeading_DiaryTitle;
                return true;
            }

            if($this->diaryHeading->getFromTo() ===  null) {
                $this->diaryHeading->setFromToFromString($line);
                $this->lastLineType = LineType::DiaryHeading_DiaryFromTo;
                return true;
            }

            if($this->diaryHeading->getAuthors() ===  null) {
                $this->diaryHeading->setAuthorsFromString($line);
                $this->lastLineType = LineType::DiaryHeading_Authors;
                return true;
            }

            if($this->diaryHeading->getYearAndPart() === null) {
                $this->diaryHeading->setYearAndPartFromString($line);
                $this->lastLineType = LineType::YearAndPart;
                $this->diaryHeadingComplete = true;

                // Add author
                $authorService = new AuthorService($this->doctrine);

                foreach($this->diaryHeading->getAuthors() as $author) {
                    $authorId = $authorService->addAuthorIfNotExists(
                        $author->getTitle(),
                        $author->getFirstname(),
                        $author->getLastname()
                    );

                    var_dump($authorId);
                    die;
                }

                // Add diary

                return true;
            }
        }
    }
}
