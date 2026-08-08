<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification generique persistee en base de donnees.
 *
 * Plutot que de creer une classe par evenement metier, on factorise sur un seul
 * type de notification parametre (titre, message, trajet concerne) : suffisant
 * pour l'exigence EF-06 ("notifications visibles dans l'interface") et facile a
 * etendre avec un canal mail plus tard si besoin (bonus notifications e-mail).
 */
class KaaydemNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $titre,
        private readonly string $message,
        private readonly ?int $tripId = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titre' => $this->titre,
            'message' => $this->message,
            'trip_id' => $this->tripId,
        ];
    }
}
