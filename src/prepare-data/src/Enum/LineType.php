<?php

namespace App\Enum;

abstract class LineType {
    const PageNumber = "PageNumber"; // bspw. [234]
    const DiaryHeading_DiaryNumber = "DiaryHeading_DiaryNumber"; // bspw. Nummer 43.
    const DiaryHeading_DiaryTitle = "DiaryHeading_DiaryTitle"; // bspw. "Tagebuch des Natur..."
    const DiaryHeading_DiaryFromTo = "DiaryHeading_DiaryFromTo"; // bspw. "Vom 7. September 1943 bis ..."
    const DiaryHeading_Authors = "DiaryHeading_Authors"; // bspw. "Geführt von [Titel] Vorn. Nachn., "
    const Empty = "EmptyLine"; // eine leere Zeile
    const YearAndPart = "YearAndPart"; // bspw. 1943, Teil 2
    const MonthAndYear = "MonthAndYear"; // bspw. September 1932
    const EntryDate = "YearAndPart"; //bspw. Donnerstag, den 9. 9. 1943.
    const EntryText = "EntryText"; // Text eines Eintrages.
}