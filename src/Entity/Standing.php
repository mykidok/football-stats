<?php

namespace App\Entity;


class Standing
{
    /** @var Table */
    private $table;

    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @param Table $table
     */
    public function setTable( Table $table): self
    {
        $this->table = $table;

        return $this;
    }







}