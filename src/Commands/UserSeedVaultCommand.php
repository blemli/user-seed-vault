<?php

namespace Blemli\UserSeedVault\Commands;

use Illuminate\Console\Command;

class UserSeedVaultCommand extends Command
{
    public $signature = 'user-seed-vault';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
