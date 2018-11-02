<?php

namespace App\Entity;


class Match
{
    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $awayTeam;

    /** @var float */
    private $previsionalNbGoals;

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    /**
     * @return Match
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
     * @return Match
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
     * @return Match
     */
    public function setPrevisionalNbGoals(float $previsionalNbGoals): self
    {
        $this->previsionalNbGoals = $previsionalNbGoals;

        return $this;
    }



}