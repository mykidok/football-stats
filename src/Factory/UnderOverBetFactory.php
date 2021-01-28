<?php


namespace App\Factory;


use App\Entity\UnderOverBet;

class UnderOverBetFactory
{
    public function constructBet(float $limit, float $averageExpectedNbGoals, int $nbGoalsExpectedMost, float $moreThanPercentage, float $lessThanPercentage, float $previsionalNbGoals): UnderOverBet
    {
        $myOdd = $nbGoalsExpectedMost > $limit ? $moreThanPercentage : $lessThanPercentage;

        $nbGoalsIsSameAsExpected = false;
        if (
            ($previsionalNbGoals > $limit && $nbGoalsExpectedMost > $limit)
            || ($previsionalNbGoals <= $limit && $nbGoalsExpectedMost <= $limit)
        ) {
            $nbGoalsIsSameAsExpected = true;
        }

        $sign = $averageExpectedNbGoals > $limit ? '+' : '-';

        return (new UnderOverBet())
            ->setType(sprintf('%s %s', $sign, $limit))
            ->setMyOdd(100/$myOdd)
            ->setPrevisionIsSameAsExpected($nbGoalsIsSameAsExpected)
        ;
    }

}