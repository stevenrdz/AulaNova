<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'question')]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Quiz $quiz;

    #[ORM\Column(type: 'string', length: 30)]
    private string $type;

    #[ORM\Column(type: 'text')]
    private string $prompt;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $correctOption = null;

    public function __construct(Quiz $quiz, string $type, string $prompt)
    {
        $this->quiz = $quiz;
        $this->type = $type;
        $this->prompt = $prompt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getCorrectOption(): ?string
    {
        return $this->correctOption;
    }

    public function setCorrectOption(?string $correctOption): self
    {
        $this->correctOption = $correctOption;

        return $this;
    }
}

