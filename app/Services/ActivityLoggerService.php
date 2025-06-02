<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityLoggerService
{

    public static function log(string $action, string $description, ?int $userId = null, ?Request $request = null): void
    {
        try {
            $currentUserId = $userId ?? (Auth::check() ? Auth::id() : null);

            $logData = [
                'user_id' => $currentUserId,
                'action' => $action,
                'description' => $description,

            ];


            Log::create($logData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to log activity: {$action} - {$description}. Error: " . $e->getMessage());
        }
    }
}
