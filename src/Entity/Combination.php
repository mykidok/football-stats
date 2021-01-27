<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CombinationRepository")
 */
class Combination
{
    const BET_AMOUNT = 20;

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

    /**
     * @var Bet[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Bet", mappedBy="combination")
     */
    private $bets;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->bets = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        $this->games->add($game);

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getGeneralOdd(): ?float
    {
        return $this->generalOdd;
    }

    public function setGeneralOdd(?float $generalOdd): self
    {
        $this->generalOdd = $generalOdd;

        return $this;
    }

    /**
     * @return Collection|Bet[]
     */
    public function getBets(): Collection
    {
        return $this->bets;
    }

    public function addBet(Bet $bet): self
    {
        $this->bets->add($bet);
        $bet->setCombination($this);

        return $this;
    }

}