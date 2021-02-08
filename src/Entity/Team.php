<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @UniqueEntity(fields={"apiId"})
 */
class Team
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
     * @var string
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $name;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true, options={"default":0})
     */
    private $nbGoalsPerMatchHome;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true, options={"default":0})
     */
    private $nbGoalsPerMatchAway;

    /**
     * @var Championship
     * @ORM\ManyToOne(targetEntity="App\Entity\Championship")
     */
    private $championship;

    /**
     * @var int
     * @ORM\Column(type="integer", unique=true)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $momentForm;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private $shortName;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $homeForceAttack;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $homeForceDefense;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $awayForceAttack;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $awayForceDefense;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $awayPlayedGames;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $homePlayedGames;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pointsMomentForm;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $bothTeamsScoreForm;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNbGoalsPerMatchHome(): ?float
    {
        return $this->nbGoalsPerMatchHome;
    }

    public function setNbGoalsPerMatchHome(?float $nbGoalsPerMatchHome): self
    {
        $this->nbGoalsPerMatchHome = $nbGoalsPerMatchHome;

        return $this;
    }

    public function getNbGoalsPerMatchAway(): ?float
    {
        return $this->nbGoalsPerMatchAway;
    }

    public function setNbGoalsPerMatchAway(?float $nbGoalsPerMatchAway): self
    {
        $this->nbGoalsPerMatchAway = $nbGoalsPerMatchAway;

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

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(?Championship $championship): self
    {
        $this->championship = $championship;

        return $this;
    }

    public function getMomentForm(): ?float
    {
        return $this->momentForm;
    }

    public function setMomentForm(?float $momentForm): self
    {
        $this->momentForm = $momentForm;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getHomeForceAttack(): ?float
    {
        return $this->homeForceAttack;
    }

    public function setHomeForceAttack(?float $homeForceAttack): self
    {
        $this->homeForceAttack = $homeForceAttack;

        return $this;
    }

    public function getHomeForceDefense(): ?float
    {
        return $this->homeForceDefense;
    }

    public function setHomeForceDefense(?float $homeForceDefense): self
    {
        $this->homeForceDefense = $homeForceDefense;

        return $this;
    }

    public function getAwayForceAttack(): ?float
    {
        return $this->awayForceAttack;
    }

    public function setAwayForceAttack(?float $awayForceAttack): self
    {
        $this->awayForceAttack = $awayForceAttack;

        return $this;
    }

    public function getAwayForceDefense(): ?float
    {
        return $this->awayForceDefense;
    }

    public function setAwayForceDefense(?float $awayForceDefense): self
    {
        $this->awayForceDefense = $awayForceDefense;

        return $this;
    }

    public function getAwayPlayedGames(): ?int
    {
        return $this->awayPlayedGames;
    }

    public function setAwayPlayedGames(?int $awayPlayedGames): self
    {
        $this->awayPlayedGames = $awayPlayedGames;

        return $this;
    }

    public function getHomePlayedGames(): ?int
    {
        return $this->homePlayedGames;
    }

    public function setHomePlayedGames(?int $homePlayedGames): self
    {
        $this->homePlayedGames = $homePlayedGames;

        return $this;
    }

    public function getPointsMomentForm(): ?int
    {
        return $this->pointsMomentForm;
    }

    public function setPointsMomentForm(?int $pointsMomentForm): self
    {
        $this->pointsMomentForm = $pointsMomentForm;

        return $this;
    }

    public function getBothTeamsScoreForm(): ?bool
    {
        return $this->bothTeamsScoreForm;
    }

    public function setBothTeamsScoreForm(?bool $bothTeamsScoreForm): self
    {
        $this->bothTeamsScoreForm = $bothTeamsScoreForm;

        return $this;
    }


}