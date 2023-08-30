<?php

namespace App\Listeners;

use Carbon\Carbon;
use App\Models\JwtToken;
use App\Events\UserAuthAttempt;

class CreateJwtToken
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //Nothing
    }

    /**
     * Handle the event.
     */
    public function handle(UserAuthAttempt $event): void
    {
        $userId = $event->user->id;
        $token = $event->token;

        $nowDatetime = Carbon::now();

        $carbon_date = Carbon::parse($nowDatetime);
        $expireAt = $carbon_date->addHours(1);

        JwtToken::updateOrCreate([
            'user_id' => $userId,
        ], [
            'unique_id' => $token,
            'token_title' => 'Authentication Token',
            'restrictions' => null,
            'permissions' => null,
            'expires_at' => $expireAt,
            'last_used_at' => $nowDatetime,
            'refreshed_at' => $nowDatetime,
        ]);
    }
}
