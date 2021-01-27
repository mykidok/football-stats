<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $previsionalNbGoals;

    /**
     * @var int|null
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
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $goodResult;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $momentForm;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $winnerMomentForm;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull()
     */
    private $odd;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull()
     */
    private $percentage;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull()
     */
    private $nbMatchForTeams;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $expectedNbGoals;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $previsionIsSameAsExpected;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageExpectedNbGoals;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $myOdd;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=true)
     */
    private $previsionalWinner;

    /**
     * @var boolean|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $winnerResult;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $winnerOdd;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    private $winner;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull()
     */
    private $winnerPercentage;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $betOnWinner = false;

    /**
     * @var Bet[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Bet", mappedBy="game", cascade={"persist"})
     */
    private $bets;

    public function __construct()
    {
        $this->bets = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getPrevisionalNbGoals(): float
    {
        return $this->previsionalNbGoals;
    }

    public function setPrevisionalNbGoals(float $previsionalNbGoals): self
    {
        $this->previsionalNbGoals = $previsionalNbGoals;

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

    public function getRealNbGoals(): ?int
    {
        return $this->realNbGoals;
    }

    public function setRealNbGoals(int $realNbGoals): self
    {
        $this->realNbGoals = $realNbGoals;

        return $this;
    }

    public function isGoodResult(): ?bool
    {
        return $this->goodResult;
    }

    public function setGoodResult(?bool $goodResult): self
    {
        $this->goodResult = $goodResult;

        return $this;
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

    public function getApiId(): int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function isMomentForm(): ?bool
    {
        return $this->momentForm;
    }

    public function setMomentForm(?bool $momentForm): self
    {
        $this->momentForm = $momentForm;

        return $this;
    }

    public function getOdd(): ?float
    {
        return $this->odd;
    }

    public function setOdd(?float $odd): self
    {
        $this->odd = $odd;

        return $this;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(?float $percentage): self
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getNbMatchForTeams(): ?int
    {
        return $this->nbMatchForTeams;
    }

    public function setNbMatchForTeams(?int $nbMatchForTeams): self
    {
        $this->nbMatchForTeams = $nbMatchForTeams;

        return $this;
    }

    public function getExpectedNbGoals(): ?int
    {
        return $this->expectedNbGoals;
    }

    public function setExpectedNbGoals(?int $expectedNbGoals): self
    {
        $this->expectedNbGoals = $expectedNbGoals;

        return $this;
    }

    public function isPrevisionIsSameAsExpected(): ?bool
    {
        return $this->previsionIsSameAsExpected;
    }

    public function setPrevisionIsSameAsExpected(?bool $previsionIsSameAsExpected): self
    {
        $this->previsionIsSameAsExpected = $previsionIsSameAsExpected;

        return $this;
    }

    public function getAverageExpectedNbGoals(): ?float
    {
        return $this->averageExpectedNbGoals;
    }

    public function setAverageExpectedNbGoals(float $averageExpectedNbGoals): self
    {
        $this->averageExpectedNbGoals = $averageExpectedNbGoals;

        return $this;
    }

    public function getMyOdd(): ?float
    {
        return $this->myOdd;
    }

    public function setMyOdd(?float $myOdd): self
    {
        $this->myOdd = $myOdd;

        return $this;
    }

    public function getPrevisionalWinner(): ?Team
    {
        return $this->previsionalWinner;
    }

    public function setPrevisionalWinner(?Team $previsionalWinner): self
    {
        $this->previsionalWinner = $previsionalWinner;

        return $this;
    }

    public function getWinnerResult(): ?bool
    {
        return $this->winnerResult;
    }

    public function setWinnerResult(?bool $winnerResult): self
    {
        $this->winnerResult = $winnerResult;

        return $this;
    }

    public function getWinnerOdd(): ?float
    {
        return $this->winnerOdd;
    }

    public function setWinnerOdd(?float $winnerOdd): self
    {
        $this->winnerOdd = $winnerOdd;

        return $this;
    }

    public function getWinnerMomentForm(): ?bool
    {
        return $this->winnerMomentForm;
    }

    public function setWinnerMomentForm(?bool $winnerMomentForm): self
    {
        $this->winnerMomentForm = $winnerMomentForm;

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getWinnerPercentage(): ?float
    {
        return $this->winnerPercentage;
    }

    public function setWinnerPercentage(?float $winnerPercentage): self
    {
        $this->winnerPercentage = $winnerPercentage;

        return $this;
    }

    public function isBetOnWinner(): bool
    {
        return $this->betOnWinner;
    }

    public function setBetOnWinner(bool $betOnWinner): self
    {
        $this->betOnWinner = $betOnWinner;

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
        $bet->setGame($this);

        return $this;
    }
}