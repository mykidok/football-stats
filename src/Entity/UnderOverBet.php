<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UnderOverBet extends Bet
{
    /** @var string */
    public const LESS_TWO_AND_A_HALF = '- 2.5';

    /** @var string */
    public const PLUS_TWO_AND_A_HALF = '+ 2.5';

    /** @var string */
    public const LESS_THREE_AND_A_HALF = '- 3.5';

    /** @var string */
    public const PLUS_THREE_AND_A_HALF = '+ 3.5';

    /** @var float  */
    public const LIMIT_2_5 = 2.5;

    /** @var float  */
    public const LIMIT_3_5 = 3.5;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $previsionIsSameAsExpected;

    public function isPrevisionIsSameAsExpected(): ?bool
    {
        return $this->previsionIsSameAsExpected;
    }

    public function setPrevisionIsSameAsExpected(?bool $previsionIsSameAsExpected): self
    {
        $this->previsionIsSameAsExpected = $previsionIsSameAsExpected;

        return $this;
    }
}