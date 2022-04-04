<?php

namespace App\Entity;

use App\Repository\AuthorToDiaryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuthorToDiaryRepository::class)]
class AuthorToDiary
{
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $author_id;

    #[ORM\Column(type: 'integer')]
    private $diary_id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }

    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
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
}
