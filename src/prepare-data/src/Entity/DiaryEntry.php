<?php

namespace App\Entity;

use App\Repository\DiaryEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiaryEntryRepository::class)]
class DiaryEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $diary_id;

    #[ORM\Column(type: 'integer')]
    private $sort_order;

    #[ORM\Column(type: 'date')]
    private $date_of_entry;

    #[ORM\Column(type: 'text')]
    private $content_text;

    #[ORM\Column(type: 'integer')]
    private $page_number;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaryId(): ?int
    {
        return $this->diary_id;
    }

    public function setDiaryId(int $diary_id): self
    {
        $this->diary_id = $diary_id;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(int $sort_order): self
    {
        $this->sort_order = $sort_order;

        return $this;
    }

    public function getDateOfEntry(): ?\DateTimeInterface
    {
        return $this->date_of_entry;
    }

    public function setDateOfEntry(\DateTimeInterface $date_of_entry): self
    {
        $this->date_of_entry = $date_of_entry;

        return $this;
    }

    public function getContentText(): ?string
    {
        return $this->content_text;
    }

    public function setContentText(string $content_text): self
    {
        $this->content_text = $content_text;

        return $this;
    }

    public function getPageNumber(): ?int
    {
        return $this->page_number;
    }

    public function setPageNumber(int $page_number): self
    {
        $this->page_number = $page_number;

        return $this;
    }
}
