<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Table
{
    /** @var Collection|Team[] */
    private $teams;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
    }

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    /**
     * @param Team
     */
    public function addTeam(Team $team): self
    {
        $this->teams->add($team);

        return $this;
    }
}