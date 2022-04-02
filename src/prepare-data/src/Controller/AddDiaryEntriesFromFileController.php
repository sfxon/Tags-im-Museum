<?php

namespace App\Controller;

use App\Enum\LineType;
use App\DTO\DiaryEntry;
use App\DTO\DiaryHeading;
use App\DTO\DiaryEntryHeading;
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
    private $sortOrder;
    
    #[Route('/addDiaryEntriesFromFile', name: 'app_add_diary_entries_from_file')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->doctrine = $doctrine;
        $this->currentPage = 0;
        $this->lastLineType = null;
        $this->diaryHeading = new DiaryHeading();
        $this->diaryHeadingComplete = false;
        $this->entryHeading = null;
        $endEntry = false;
        $entryHeading = null;
        $this->sortOrder = 1;
        $lines = [];
        $isEntryHeading = false;

        $file = new \SplFileObject("../../../data/tagebuecher-plain-text-separated/02.txt");

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
                $isEntryHeading = true;
            } else {
                $lines[] = $line;
            }

            // Eintrag speichern, wenn dies das Ende ist.
            if($endEntry) {
                // Entferne leere Zeilen am Ende des Zeilen-Arrays.
                while(count($lines) > 0 && strlen(trim($lines[(count($lines) - 1)])) == 0) {
                    unset($lines[(count($lines) - 1)]);
                }

                // Wenn das der erste Eintrag ist
                if($entryHeading == null && $this->sortOrder == 1 && $isEntryHeading) {
                    $entryHeading = $this->getEntryHeading($line);
                } else {
                    // Wenn es noch Zeilen gibt.
                    if(count($lines) > 0) {
                        $newEntryHeading = $this->getEntryHeading($line);

                        if(null !== $entryHeading) {
                            $this->saveEntry($entryHeading, $lines);
                            $lines = [];
                        }
                        
                        $entryHeading = $newEntryHeading;
                    }
                }
            }

            $endEntry = false;
            $isEntryHeading = false;
        };

        // Letzten Eintrag einfügen
        // Entferne leere Zeilen am Ende des Zeilen-Arrays.
        while(count($lines) > 0 && strlen(trim($lines[(count($lines) - 1)])) == 0) {
            unset($lines[(count($lines) - 1)]);
        }
        
        if(count($lines) > 0) {
            $this->saveEntry($entryHeading, $lines);
        }

        // Dateizeiger auf null setzen, Context damit schließen.
        $file = null;


        /*return $this->render('add_diary_entries_from_file/index.html.twig', [
            'controller_name' => 'AddDiaryEntriesFromFileController',
        ]);*/

        die('Daten wurden importiert.');
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

    private function getEntryHeading($line) {
        // Explode on Komma
        $parts = explode(',', $line);

        // zwei Teile?
        if(!is_array($parts) || count($parts) != 2) {
            return null;
        }

        // im ersten Teil: Wochentag parsen
        if(strlen($parts[0]) > 10) {        // Längster String ist "Donnerstag", mit 10 Buchstaben.
            return null;
        }

        $weekday = DateHelper::getWeekdayByName($parts[0]);

        if(false === $weekday) {
            return null;
        }

        // im zweiten Teil: den enthalten?


        if(strpos($parts[1], " den ") === false) {
            return null;
        }

        $dateString = str_replace(" den ", "", $parts[1]);

        // im zweiten Teil: endet auf einen Punkt?
        if(strpos(strrev($dateString), ".") != 0) {
            return null;
        }

        // den . entfernen
        $dateString = rtrim($dateString, '.');
        
        // im zweiten Teil das Datum parsen
        $dateString = DateHelper::parseNumericTextualDate($dateString);

        $diaryEntryHeading = new DiaryEntryHeading();
        $diaryEntryHeading->setWeekday($weekday);
        $diaryEntryHeading->setEntryDateByString($dateString);
        
        return $diaryEntryHeading;
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

                $this->saveDiaryDataInDb();

                return true;
            }
        }
    }

    private function saveDiaryDataInDb() {
        // Add author
        $authorService = new AuthorService($this->doctrine);

        foreach($this->diaryHeading->getAuthors() as $author) {
            $authorId = $authorService->addAuthorIfNotExists(
                $author->getTitle(),
                $author->getFirstname(),
                $author->getLastname()
            );

            $this->authorIds[] = $authorId;
        }

        if(count($this->authorIds) != 1) {
            die('Es ist mehr als ein Author vorhanden. Wem sollen wir die Einträge zuordnen?');
        }

        // Add diary
        $diaryService = new DiaryService($this->doctrine);

        $this->diaryId = $diaryService->addDiaryIfNotExists(
            $this->diaryHeading->getNumber(),
            $this->diaryHeading->getTitle(),
            $this->diaryHeading->getFromAsDatetime(),
            $this->diaryHeading->getToAsDatetime(),
            $this->diaryHeading->getPart()
        );
    }

    public function saveEntry($entryHeading, $lines) {
        $diaryEntryService = new DiaryEntryService($this->doctrine);
        
        $diaryEntryId = $diaryEntryService->addDiaryEntry(
            $this->diaryId, 
            $this->sortOrder,
            $entryHeading->getEntryDate(),
            implode("\n", $lines)
        );

        // Add lines
        $diaryEntryLineService = new DiaryEntryLinesService($this->doctrine);
        $sortOrder = 1;
        
        foreach($lines as $line) {
            $diaryEntryLineService->addDiaryEntryLine(
                $diaryEntryId, 
                $sortOrder, 
                $line
            );

            $sortOrder++;
        }

        $this->sortOrder++;
    }
}
