<?php

namespace App\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc
     */
    public function transform($value)
    {
        if (empty($value)) {
            return;
        }

        return new \DateTime($value);
    }

    /**
     * {@inheritdoc
     */
    public function reverseTransform($value)
    {
        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : null;
    }
}