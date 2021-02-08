<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BothTeamsScoreBet extends Bet
{
    /** @var string */
    public const BOTH_TEAMS_GOAL_TYPE = 'both_teams_score';

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $bothTeamsScore = false;

    public function isBothTeamsScore(): bool
    {
        return $this->bothTeamsScore;
    }

    public function setBothTeamsScore(bool $bothTeamsScore): self
    {
        $this->bothTeamsScore = $bothTeamsScore;

        return $this;
    }



}