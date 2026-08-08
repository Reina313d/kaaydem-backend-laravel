# Kaay Dem ! — API Backend (Laravel)

API REST de la plateforme de covoiturage étudiant **Kaay Dem !**, développée avec
Laravel 11 et Laravel Sanctum (authentification par tokens), conformément au cahier
des charges ISEP Diamniadio.

## Stack technique

- PHP 8.2+ / Laravel 11
- Laravel Sanctum (authentification API par tokens)
- MySQL ou PostgreSQL
- Architecture : contrôleurs fins → Form Requests → Services métier → API Resources

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurez votre base de données dans `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`),
puis lancez :

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

L'API est alors disponible sur `http://localhost:8000/api/v1`.

### Comptes de démonstration (mot de passe : `password` pour tous)

| Rôle                      | Email                     |
|---------------------------|---------------------------|
| Administrateur            | admin@kaaydem.sn          |
| Conducteur (validé)       | conducteur1@kaaydem.sn    |
| Conducteur (en attente)   | conducteur2@kaaydem.sn    |
| Passager                  | passager1@kaaydem.sn      |
| Passager                  | passager2@kaaydem.sn      |

## Structure du projet

```
app/
  Enums/            TripStatus, ReservationStatus, DriverValidationStatus, ReportStatus
  Exceptions/       PlacesInsuffisantesException, TrajetNonModifiableException, CreneauChevauchantException
  Http/
    Controllers/Api/V1/     Contrôleurs API (fins, délèguent aux services)
    Controllers/Api/V1/Admin/  Contrôleurs de l'espace administrateur
    Requests/                Form Requests (validation)
    Resources/                API Resources (sérialisation)
    Middleware/EnsureRole.php Contrôle d'accès par rôle
  Models/            User, DriverProfile, Trip, Reservation, Review, Report
  Policies/          TripPolicy, ReservationPolicy
  Services/          ReservationService, TripService, StatsService (logique métier)
database/
  migrations/        Schéma complet (soft deletes sur trips/reservations)
  factories/         Factories pour tous les modèles
  seeders/           DatabaseSeeder (jeu de données de démonstration)
routes/api.php       Toutes les routes, préfixées /api/v1
```

## Points clés d'architecture

- **Gestion du double rôle passager/conducteur** : un même `User` peut avoir un
  `DriverProfile` (statut `en_attente` / `valide` / `rejete`). Le rôle conducteur
  n'est actif qu'une fois validé par l'admin (`estConducteurValide()`), vérifié à la
  fois par le middleware `role:conducteur` et dans `TripController::store`.
- **Cycle de vie des réservations** : `en_attente → confirmée → terminée`, ou
  `en_attente → refusée / annulée`, avec historisation de chaque transition
  (`historique_transitions` en JSON) et décrémentation/incrémentation atomique des
  places disponibles (verrou pessimiste `lockForUpdate` dans `ReservationService`).
- **Erreurs métier** : `PlacesInsuffisantesException` et
  `CreneauChevauchantException` renvoient un code **409**, gérées globalement dans
  `bootstrap/app.php`.
- **Autorisations** : policies Laravel (`TripPolicy`, `ReservationPolicy`) pour toute
  action sensible (modification, décision sur une réservation, évaluation).

## Documentation de l'API

Voir le tableau des routes dans `routes/api.php`. Toutes les routes protégées
nécessitent l'en-tête `Authorization: Bearer {token}` obtenu via `/api/v1/login`
ou `/api/v1/register`.

## Tests & CI

Un workflow GitHub Actions (`.github/workflows/ci.yml`) exécute les migrations et
la suite de tests PHPUnit à chaque push sur une base MySQL de service.
