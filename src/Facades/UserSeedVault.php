<?php

namespace Blemli\UserSeedVault\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Blemli\UserSeedVault\UserSeedVault
 */
class UserSeedVault extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Blemli\UserSeedVault\UserSeedVault::class;
    }
}
