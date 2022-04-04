<?php

namespace App\Controller;

use App\Enum\LineType;
use App\DTO\DiaryEntry;
use App\DTO\DiaryHeading;
use App\DTO\DiaryEntryHeading;
use App\Services\DateHelper;
use App\Services\AuthorService;
use App\Services\AuthorToDiaryService;
use App\Services\DiaryService;
use App\Services\DiaryEntryService;
use App\Services\DiaryEntryLinesService;
use App\Services\DiaryPageNumberCheck;
use App\Services\LineProcessing;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddDiaryEntriesFromFileController extends AbstractController
{
    private $request;
    private $currentPage = 0;
    private $lastReadPageNumber = 0;
    private $lastLineType = null;
    private $diaryHeading = null;
    private $diaryHeadingComplete = false;
    private $diarySection = null;
    private $authorIds = [];
    private $diaryId;
    private $doctrine;
    private $sortOrder;
    private $shortDates = false;
    
    #[Route('/addDiaryEntriesFromFile', name: 'app_add_diary_entries_from_file')]
    public function index(
        ManagerRegistry $doctrine,
        Request $request): Response
    {
        $this->doctrine = $doctrine;
        $this->request = $request;

        // Wir unterscheiden zwischen diesen beiden Zeilennummern, weil Beiträge sich über eine oder mehrere Seiten hinweg erstrecken können.
        // currentPage wird dabei immer mit lastReadPageNumber aktualisiert, wenn ein Eintrag geschrieben wurd.
        $this->lastReadPageNumber = 0;      // Zuletzt gelesene Zeilennummer
        $this->currentPage = 0;             // Aktuelle Zeilennummer, die eingetragen wird.
        
        $this->lastLineType = null;
        $this->diaryHeading = new DiaryHeading();
        $this->diaryHeadingComplete = false;
        $this->entryHeading = null;
        $endEntry = false;
        $entryHeading = null;
        $lines = [];
        $isEntryHeading = false;
        $this->shortDates = $this->getShortDates();

        $filename = $this->getFilename();
        $this->sortOrder = $this->getSortOrder();

        $file = new \SplFileObject("../../../data/tagebuecher-plain-text-separated/" . basename($filename)); //02b.txt");

        while(!$file->eof()) 
        {
            $line =  $file->fgets();
            $line = trim($line);
            
            // Prüfe Zeilen auf unterschiedliche Typen
            $pageNumber = DiaryPageNumberCheck::getPageNumberFromString($line);
            
            if(false !== $pageNumber) {
                if($this->currentPage == 0 || $this->sortOrder == 1) {
                    $this->currentPage = $pageNumber;
                }

                $this->lastReadPageNumber = $pageNumber;

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
                            $this->currentPage = $this->lastReadPageNumber;
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

        die('Daten wurden importiert. Sort-Order für Tagebucheinträge: ' . $this->sortOrder);
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
        if($this->shortDates) {
            return $this->isEntryHeadingShortDates($line);
        }

        return $this->isEntryHeadingLongDates($line);
    }

    private function isEntryHeadingLongDates($line) {
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
        
        return true;
    }

    private function isEntryHeadingShortDates($line) {
        $dateString = $line;

        // im zweiten Teil: endet auf einen Punkt?
        if(strpos(strrev($dateString), ".") != 0) {
            return false;
        }

        $dateString = rtrim($dateString, ".");

        $result = DateHelper::parseNumericTextualDate($dateString);

        if($result === false) {
            return false;
        }

        return true;
    }

    private function getEntryHeading($line) {
        if($this->shortDates) {
            return $this->getEntryHeadingShortDates($line);
        }

        return $this->getEntryHeadingLongDates($line);
    }

    private function getEntryHeadingLongDates($line) {
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

    private function getEntryHeadingShortDates($line) {
        $dateString = $line;

        // im zweiten Teil: endet auf einen Punkt?
        if(strpos(strrev($dateString), ".") != 0) {
            return false;
        }

        $dateString = rtrim($dateString, ".");
        
        // im zweiten Teil das Datum parsen
        $sqlDate = DateHelper::parseNumericTextualDate($dateString);

        if(false === $sqlDate) {
            $tmp = $this->isEntryHeadingShortDates($line);

            var_dump($tmp);

            var_dump($dateString);
            die;
        }

        // Get Weekday
        $weekday = DateHelper::getWeekdayFromSqlDate($sqlDate);

        $diaryEntryHeading = new DiaryEntryHeading();
        $diaryEntryHeading->setWeekday($weekday);
        $diaryEntryHeading->setEntryDateByString($sqlDate);
        
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
                $this->saveAuthorToDiaryDataInDb();

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
            $setAllAuthors = $this->request->query->get('set_all_authors');

            if("true" !== $setAllAuthors) {
                die('Es ist mehr als ein Author vorhanden. Wem sollen wir die Einträge zuordnen? (Verarbeitung kann mit ?set_all_authors=true forciert werden.');
            }
        }

        // Add diary
        $diaryService = new DiaryService($this->doctrine);

        $this->diaryId = $diaryService->upsert(
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
            $this->currentPage,
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

    private function saveAuthorToDiaryDataInDb() {
        $authorToDiaryService = new AuthorToDiaryService($this->doctrine);

        foreach($this->authorIds as $authorId) {
            $authorToDiaryService->upsert($authorId, $this->diaryId );
        }
    }

    private function getFilename() {
        // $_GET parameters
        $filename = basename($this->request->query->get('filename'));
        return $filename;
    }

    private function getSortOrder() {
        // $_GET parameters
        $sortOrder = (int)$this->request->query->get('sort_order');
        return $sortOrder;
    }

    // Prüft, ob der Request-Parameter "short_dates" auf "true" gesetzt ist.
    // Falls ja, verwendet das Programm zur Feststellung des Datums keinen vorangestellten Wochentag.
    private function getShortDates() {
        // $_GET parameters
        $short_dates = $this->request->query->get('short_dates');

        if("true" === $short_dates) {
            return true;
        }
        return false;
    }
}
