<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('pos:auto-draft-po')->dailyAt('06:00');
Schedule::command('pos:auto-expire-subscriptions')->dailyAt('00:00');
