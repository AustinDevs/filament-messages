<?php

namespace AustinDevs\FilamentMessages\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AustinDevs\FilamentMessages\FilamentMessages
 */
class FilamentMessages extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \AustinDevs\FilamentMessages\FilamentMessages::class;
    }
}
