# Kaay Dem ! — Documentation de l'API v1

Base URL : `http://localhost:8000/api/v1`
Authentification : **Bearer token** (Laravel Sanctum), obtenu via `/register` ou `/login`, à transmettre dans l'en-tête `Authorization: Bearer {token}` pour toutes les routes protégées.

Codes d'erreur normalisés : `401` (non authentifié), `403` (non autorisé), `404` (introuvable), `409` (conflit métier), `422` (validation).

---

## Authentification (public)

| Méthode | Route | Description | Corps de la requête |
|---|---|---|---|
| POST | `/register` | Inscription d'un nouvel utilisateur | `nom, email, password, password_confirmation, telephone?, campus?` |
| POST | `/login` | Connexion, retourne `{ user, token }` | `email, password` |
| POST | `/logout`  | Déconnexion (révoque le token courant) | — |

## Profil 

| Méthode | Route | Description | Corps de la requête |
|---|---|---|---|
| GET | `/me` | Voir mon profil (avec `driver_profile` si existant) | — |
| PUT | `/me` | Modifier mon profil | `nom?, email?, telephone?, campus?, photo?, password?` |
| POST | `/driver-requests` | Demander le statut conducteur | `numero_permis, vehicule_marque, vehicule_modele, vehicule_immatriculation` |
| GET | `/driver-requests/me` | Consulter le statut de ma demande conducteur | — |
| POST | `/reports` | Signaler un utilisateur | `utilisateur_signale_id, motif` |

## Trajets

| Méthode | Route | Auth | Description | Paramètres |
|---|---|---|---|---|
| GET | `/trips` | Public | Recherche paginée de trajets publiés | query : `depart, arrivee, date, prix_max, places_min, tri, direction, par_page` |
| GET | `/trips/{id}` | Public | Détail d'un trajet | — |
| POST | `/trips` | 🔒 Conducteur validé | Publier un trajet | `ville_depart, ville_arrivee, points_arret?, date_heure_depart, places_totales, prix_place` |
| PUT | `/trips/{id}` | 🔒 Propriétaire | Modifier un trajet (refusé si réservation confirmée → 409) | champs modifiables, tous optionnels |
| DELETE | `/trips/{id}` | 🔒 Propriétaire | Annuler un trajet (refusé si réservation confirmée → 409) | — |
| PATCH | `/trips/{id}/close` | 🔒 Propriétaire | Clôturer un trajet (passe les réservations confirmées à "terminée") | — |
| GET | `/me/trips` | 🔒 | Mes trajets publiés + réservations reçues | query : `par_page` |

## Réservations 🔒

| Méthode | Route | Auth | Description | Corps / codes particuliers |
|---|---|---|---|---|
| POST | `/trips/{id}/reservations` | Passager | Réserver des places sur un trajet | `nombre_places` — 409 si places insuffisantes ou créneau chevauchant |
| PATCH | `/reservations/{id}/accept` | Conducteur du trajet | Accepter une réservation en attente | — |
| PATCH | `/reservations/{id}/refuse` | Conducteur du trajet | Refuser une réservation (recrédite les places) | — |
| PATCH | `/reservations/{id}/cancel` | Passager ou conducteur | Annuler une réservation (recrédite les places si en attente/confirmée) | — |
| GET | `/me/reservations` | Passager | Mes réservations | query : `par_page` |

## Évaluations 🔒

| Méthode | Route | Auth | Description | Corps de la requête |
|---|---|---|---|---|
| POST | `/reservations/{id}/review` | Passager de la réservation | Évaluer le conducteur (uniquement si trajet terminé, une seule fois) | `note (1-5), commentaire?` |

## Administration 🔒 (rôle admin uniquement)

| Méthode | Route | Description | Paramètres |
|---|---|---|---|
| GET | `/admin/users` | Liste paginée des utilisateurs | query : `recherche, par_page` |
| PATCH | `/admin/users/{id}/toggle-active` | Activer / désactiver un compte | — |
| GET | `/admin/reports` | Liste des signalements | query : `statut, par_page` |
| PATCH | `/admin/reports/{id}` | Changer le statut d'un signalement | `statut` (ouvert / en_cours_traitement / resolu / rejete) |
| GET | `/admin/driver-requests` | Liste des demandes de statut conducteur | query : `statut, par_page` |
| PATCH | `/admin/driver-requests/{id}` | Valider ou rejeter une demande conducteur | `statut_validation (valide/rejete), motif_rejet?` |
| GET | `/admin/stats` | Statistiques : trajets/mois, taux d'occupation, top conducteurs, totaux | — |

---

## Comptes de test (mot de passe : `password`)

| Rôle | Email |
|---|---|
| Administrateur | admin@kaaydem.sn |
| Conducteur (validé) | conducteur1@kaaydem.sn |
| Conducteur (en attente) | conducteur2@kaaydem.sn |
| Passager | passager1@kaaydem.sn |
| Passager | passager2@kaaydem.sn |

## Utilisation de la collection Postman

1. Importez `kaaydem-api.postman_collection.json` dans Postman (**Import → File**)
2. Ouvrez **Authentification → Connexion**, exécutez la requête avec un compte de test : le token est automatiquement enregistré dans la variable de collection `token` (script de test intégré)
3. Toutes les autres requêtes utilisent ce token automatiquement (authentification de collection en Bearer)
4. Pensez à ajuster les variables `trip_id`, `reservation_id`, `user_id`, etc. selon les données réellement présentes dans votre base
