<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 * @UniqueEntity(fields={"apiId"})
 */
class Game
{
    const LIMIT = 2.5;

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
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $homeTeam;

    /**
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $awayTeam;

    /**
     * @var Championship
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championship", inversedBy="games")
     */
    private $championship;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $previsionalNbGoals;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $realNbGoals;

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
    private $goodResult;

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @return Game
     */
    public function setHomeTeam(Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): Team
    {
        return $this->awayTeam;
    }

    /**
     * @return Game
     */
    public function setAwayTeam(Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getPrevisionalNbGoals(): float
    {
        return $this->previsionalNbGoals;
    }

    /**
     * @return Game
     */
    public function setPrevisionalNbGoals(float $previsionalNbGoals): self
    {
        $this->previsionalNbGoals = $previsionalNbGoals;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return Game
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRealNbGoals(): int
    {
        return $this->realNbGoals;
    }

    /**
     * @return Game
     */
    public function setRealNbGoals(int $realNbGoals): self
    {
        $this->realNbGoals = $realNbGoals;

        return $this;
    }

    public function isGoodResult(): bool
    {
        return $this->goodResult;
    }

    /**
     * @return Game
     */
    public function setGoodResult(bool $goodResult): self
    {
        $this->goodResult = $goodResult;

        return $this;
    }

    public function getChampionship(): Championship
    {
        return $this->championship;
    }

    /**
     * @return Game
     */
    public function setChampionship(Championship $championship): self
    {
        $this->championship = $championship;

        return $this;
    }

    public function getApiId(): int
    {
        return $this->apiId;
    }

    /**
     * @return Game
     */
    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }
}