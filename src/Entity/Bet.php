<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="bet")
 * @ORM\Entity(repositoryClass="App\Repository\BetRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "winner"="App\Entity\WinnerBet",
 *     "under_over"="App\Entity\UnderOverBet",
 *     "both_teams_score"="App\Entity\BothTeamsScoreBet",
 * })
 */
abstract class Bet
{
    /** @var float */
    public const MINIMUM_ODD = 1.35;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    protected $odd;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $goodResult;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $form;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    protected $percentage;

    /**
     * @var Game|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="bets")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $game;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     */
    protected $myOdd;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    public function getId(): int
    {
        return $this->id;
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

    public function isGoodResult(): ?bool
    {
        return $this->goodResult;
    }

    public function setGoodResult(?bool $goodResult): self
    {
        $this->goodResult = $goodResult;

        return $this;
    }

    public function isForm(): ?bool
    {
        return $this->form;
    }

    public function setForm(?bool $form): self
    {
        $this->form = $form;

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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}