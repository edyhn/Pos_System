<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class AutoExpireSubscriptions extends Command
{
    protected $signature = 'pos:auto-expire-subscriptions';
    protected $description = 'Expire subscriptions that have passed their end date';

    public function handle()
    {
        $expired = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info("{$expired} subscription(s) marked as expired.");
    }
}
