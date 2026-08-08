<?php

namespace App\Models;

use App\Enums\DriverValidationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numero_permis',
        'vehicule_marque',
        'vehicule_modele',
        'vehicule_immatriculation',
        'statut_validation',
        'motif_rejet',
    ];

    protected function casts(): array
    {
        return [
            'statut_validation' => DriverValidationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
