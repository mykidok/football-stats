<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Combination
{
    const BET_AMOUNT = 10;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Game[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Game")
     */
    private $games;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $success;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $generalOdd;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    /**
     * @return Combination
     */
    public function addGame(Game $game): self
    {
        $this->games->add($game);

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return Combination
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @return Combination
     */
    public function setSuccess(?bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getGeneralOdd(): ?float
    {
        return $this->generalOdd;
    }

    /**
     * @return Combination
     */
    public function setGeneralOdd(?float $generalOdd): self
    {
        $this->generalOdd = $generalOdd;

        return $this;
    }
}