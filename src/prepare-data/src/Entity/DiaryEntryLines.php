<?php

namespace App\Entity;

use App\Repository\DiaryEntryLinesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiaryEntryLinesRepository::class)]
class DiaryEntryLines
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $diary_entry_id;

    #[ORM\Column(type: 'integer')]
    private $sort_order;

    #[ORM\Column(type: 'string', length: 4096, nullable: true)]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaryEntryId(): ?int
    {
        return $this->diary_entry_id;
    }

    public function setDiaryEntryId(int $diary_entry_id): self
    {
        $this->diary_entry_id = $diary_entry_id;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
