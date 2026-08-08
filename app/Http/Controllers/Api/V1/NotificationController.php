<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Notifications de l'utilisateur connecte, les plus recentes d'abord.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate($request->query('par_page', 10));

        return NotificationResource::collection($notifications);
    }

    public function markRead(Request $request, DatabaseNotification $notification)
    {
        $this->verifierProprietaire($request, $notification);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return new NotificationResource($notification);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Toutes les notifications ont ete marquees comme lues.']);
    }

    private function verifierProprietaire(Request $request, DatabaseNotification $notification): void
    {
        if (
            $notification->notifiable_id !== $request->user()->id
            || $notification->notifiable_type !== $request->user()->getMorphClass()
        ) {
            abort(403, "Cette notification ne vous appartient pas.");
        }
    }
}
