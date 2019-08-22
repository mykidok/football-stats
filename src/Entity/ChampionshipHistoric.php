<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"championship", "season"},
 *     errorPath="championship",
 *     message="This championship already has an historic for this season."
 * )
 */
class ChampionshipHistoric
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Championship
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championship")
     */
    private $championship;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $season;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float")
     */
    private $averageGoalsHomeFor;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float")
     */
    private $averageGoalsHomeAgainst;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float")
     */
    private $averageGoalsAwayFor;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float")
     */
    private $averageGoalsAwayAgainst;

    public function getId(): int
    {
        return $this->id;
    }

    public function getChampionship(): Championship
    {
        return $this->championship;
    }

    public function setChampionship(Championship $championship): self
    {
        $this->championship = $championship;

        return $this;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function setSeason(int $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getAverageGoalsHomeFor(): float
    {
        return $this->averageGoalsHomeFor;
    }

    public function setAverageGoalsHomeFor(float $averageGoalsHomeFor): self
    {
        $this->averageGoalsHomeFor = $averageGoalsHomeFor;

        return $this;
    }

    public function getAverageGoalsHomeAgainst(): float
    {
        return $this->averageGoalsHomeAgainst;
    }

    public function setAverageGoalsHomeAgainst(float $averageGoalsHomeAgainst): self
    {
        $this->averageGoalsHomeAgainst = $averageGoalsHomeAgainst;

        return $this;
    }

    public function getAverageGoalsAwayFor(): float
    {
        return $this->averageGoalsAwayFor;
    }

    public function setAverageGoalsAwayFor(float $averageGoalsAwayFor): self
    {
        $this->averageGoalsAwayFor = $averageGoalsAwayFor;

        return $this;
    }

    public function getAverageGoalsAwayAgainst(): float
    {
        return $this->averageGoalsAwayAgainst;
    }

    public function setAverageGoalsAwayAgainst(float $averageGoalsAwayAgainst): self
    {
        $this->averageGoalsAwayAgainst = $averageGoalsAwayAgainst;

        return $this;
    }
}
