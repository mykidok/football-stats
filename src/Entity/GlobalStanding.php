<?php

namespace App\Entity;


class GlobalStanding
{
    /** @var Standing */
    private $homeStanding;

    /** @var Standing */
    private $awayStanding;

    public function getHomeStanding(): Standing
    {
        return $this->homeStanding;
    }

    /**
     * @param Standing $homeStanding
     */
    public function setHomeStanding(Standing $homeStanding): self
    {
        $this->homeStanding = $homeStanding;

        return $this;
    }

    public function getAwayStanding(): Standing
    {
        return $this->awayStanding;
    }

    /**
     * @param Standing $awayStanding
     */
    public function setAwayStanding(Standing $awayStanding): self
    {
        $this->awayStanding = $awayStanding;

        return $this;
    }



}