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
     *
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @var Game[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="championship")
     */
    private $games;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull()
     */
    private $apiId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150)
     */
    private $logo;

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

    /**
     * @return Championship
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApiId(): int
    {
        return $this->apiId;
    }

    /**
     * @return Championship
     */
    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    /**
     * @return $this
     */
    public function addGame(Game $game): self
    {
        $this->games->add($game);

        return $this;
    }
}
