<?php


namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Country
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
     * @ORM\Column(type="string", length=60)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=150)
     */
    private $flagPath;

    /**
     * @var Championship[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Championship", mappedBy="country")
     */
    private $championships;

    public function __construct()
    {
        $this->championships = new ArrayCollection();
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

    public function getFlagPath(): string
    {
        return $this->flagPath;
    }

    public function setFlagPath(string $flagPath): self
    {
        $this->flagPath = $flagPath;

        return $this;
    }

    /**
     * @return Championship[]|Collection
     */
    public function getChampionships(): Collection
    {
        return $this->championships;
    }
}