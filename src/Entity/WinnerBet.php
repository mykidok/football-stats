<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class WinnerBet extends Bet
{
    /** @var int */
    public const WIN_OR_DRAW_DIFFERENCE = 15;

    /** @var string */
    public const WINNER_TYPE = 'winner';

    /**
     * @var Team|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    private $winner;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $winOrDraw = false;

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function isWinOrDraw(): bool
    {
        return $this->winOrDraw;
    }

    public function setWinOrDraw(bool $winOrDraw): self
    {
        $this->winOrDraw = $winOrDraw;

        return $this;
    }
}