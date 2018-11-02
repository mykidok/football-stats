<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MatchDay
{
    /** @var Match[]|Collection */
    private $matches;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
    }


    public function getMatches(): Collection
    {
        return $this->matches;
    }

    /**
     * @param Match $matches
     */
    public function addMatch(Match $match): MatchDay
    {
        $this->matches->add($match);

        return $this;
    }



}