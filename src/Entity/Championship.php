<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampionshipRepository")
 * @UniqueEntity(fields={"apiId"})
 */
class Championship
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
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @var Game[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="championship")
     */
    private $games;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @var string
     * @ORM\Column(type="string", length=150)
     */
    private $logo;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageGoalsHomeFor;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageGoalsHomeAgainst;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageGoalsAwayFor;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageGoalsAwayAgainst;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="championships")
     */
    private $country;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotNull()
     */
    private $startDate;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

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

    public function getApiId(): int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
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

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function setLogo(string $logo)
    {
        $this->logo = $logo;
    }

    public function getAverageGoalsHomeFor(): ?float
    {
        return $this->averageGoalsHomeFor;
    }

    public function setAverageGoalsHomeFor(?float $averageGoalsHomeFor): self
    {
        $this->averageGoalsHomeFor = $averageGoalsHomeFor;

        return $this;
    }

    public function getAverageGoalsHomeAgainst(): ?float
    {
        return $this->averageGoalsHomeAgainst;
    }

    public function setAverageGoalsHomeAgainst(?float $averageGoalsHomeAgainst): self
    {
        $this->averageGoalsHomeAgainst = $averageGoalsHomeAgainst;

        return $this;
    }

    public function getAverageGoalsAwayFor(): ?float
    {
        return $this->averageGoalsAwayFor;
    }

    public function setAverageGoalsAwayFor(?float $averageGoalsAwayFor): self
    {
        $this->averageGoalsAwayFor = $averageGoalsAwayFor;

        return $this;
    }

    public function getAverageGoalsAwayAgainst(): ?float
    {
        return $this->averageGoalsAwayAgainst;
    }

    public function setAverageGoalsAwayAgainst(?float $averageGoalsAwayAgainst): self
    {
        $this->averageGoalsAwayAgainst = $averageGoalsAwayAgainst;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }


}
