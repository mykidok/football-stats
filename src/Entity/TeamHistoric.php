<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"team", "season"},
 *     errorPath="team",
 *     message="This team already has an historic for this season."
 * )
 */
class TeamHistoric
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
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    private $team;

    /**
     * @var ChampionshipHistoric
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ChampionshipHistoric")
     */
    private $championshipHistoric;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $season;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $homeForceAttack;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $homeForceDefense;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $awayForceAttack;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $awayForceDefense;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

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

    public function getHomeForceAttack(): float
    {
        return $this->homeForceAttack;
    }

    public function setHomeForceAttack(?float $homeForceAttack): self
    {
        $this->homeForceAttack = $homeForceAttack;

        return $this;
    }

    public function getHomeForceDefense(): float
    {
        return $this->homeForceDefense;
    }

    public function setHomeForceDefense(float $homeForceDefense): self
    {
        $this->homeForceDefense = $homeForceDefense;

        return $this;
    }

    public function getAwayForceAttack(): float
    {
        return $this->awayForceAttack;
    }

    public function setAwayForceAttack(float $awayForceAttack): self
    {
        $this->awayForceAttack = $awayForceAttack;

        return $this;
    }

    public function getAwayForceDefense(): float
    {
        return $this->awayForceDefense;
    }

    public function setAwayForceDefense(float $awayForceDefense): self
    {
        $this->awayForceDefense = $awayForceDefense;

        return $this;
    }

    public function getChampionshipHistoric(): ChampionshipHistoric
    {
        return $this->championshipHistoric;
    }

    public function setChampionshipHistoric(ChampionshipHistoric $championshipHistoric): self
    {
        $this->championshipHistoric = $championshipHistoric;

        return $this;
    }
}