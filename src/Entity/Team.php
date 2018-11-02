<?php

namespace App\Entity;


class Team
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var float */
    private $nbGoalsPerMatch;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Team
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Team
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNbGoalsPerMatch(): float
    {
        return $this->nbGoalsPerMatch;
    }

    /**
     * @return Team
     */
    public function setNbGoalsPerMatch(float $nbGoalsPerMatch): self
    {
        $this->nbGoalsPerMatch = $nbGoalsPerMatch;

        return $this;
    }



}