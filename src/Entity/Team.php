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
     *
     * @ORM\Column(type="string", length=60, nullable=false, unique=true)
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default":0})
     */
    private $nbGoalsPerMatchHome;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default":0})
     */
    private $nbGoalsPerMatchAway;

    /**
     * @var Championship
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championship")
     * @ORM\JoinColumn()
     */
    private $championship;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true, nullable=false)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull()
     */
    private $momentForm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private $shortName;

    public function getId(): int
    {
        return $this->id;
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

    public function getNbGoalsPerMatchHome(): float
    {
        return $this->nbGoalsPerMatchHome;
    }

    /**
     * @return Team
     */
    public function setNbGoalsPerMatchHome(float $nbGoalsPerMatchHome): self
    {
        $this->nbGoalsPerMatchHome = $nbGoalsPerMatchHome;

        return $this;
    }

    public function getNbGoalsPerMatchAway(): float
    {
        return $this->nbGoalsPerMatchAway;
    }

    /**
     * @return Team
     */
    public function setNbGoalsPerMatchAway(float $nbGoalsPerMatchAway): self
    {
        $this->nbGoalsPerMatchAway = $nbGoalsPerMatchAway;

        return $this;
    }

    public function getApiId(): int
    {
        return $this->apiId;
    }

    /**
     * @return Team
     */
    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function getChampionship(): Championship
    {
        return $this->championship;
    }

    /**
     * @return Team
     */
    public function setChampionship(Championship $championship): self
    {
        $this->championship = $championship;

        return $this;
    }

    public function getMomentForm(): ?float
    {
        return $this->momentForm;
    }

    /**
     * @return Team
     */
    public function setMomentForm(?float $momentForm): self
    {
        $this->momentForm = $momentForm;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * @return Team
     */
    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }


}