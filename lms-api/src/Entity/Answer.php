<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'answer')]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Attempt::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Attempt $attempt;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $answerText = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isCorrect = null;

    public function __construct(Attempt $attempt, Question $question)
    {
        $this->attempt = $attempt;
        $this->question = $question;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttempt(): Attempt
    {
        return $this->attempt;
    }

    public function setAttempt(Attempt $attempt): self
    {
        $this->attempt = $attempt;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswerText(): ?string
    {
        return $this->answerText;
    }

    public function setAnswerText(?string $answerText): self
    {
        $this->answerText = $answerText;

        return $this;
    }

    public function getIsCorrect(): ?bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(?bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }
}

