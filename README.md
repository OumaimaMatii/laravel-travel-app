# Laravel Travel App

Application de réservation de voyages avec gestion de forfaits et voyages sur mesure.

## Technologies utilisées

- **Backend** : Laravel 11, PHP 8.2
- **Frontend** : React.js, Redux Toolkit
- **Base de données** : MySQL
- **Authentification** : Laravel Sanctum
- **Styles** : Tailwind CSS

## Fonctionnalités

### Clients
- Consultation des forfaits
- Réservation de voyages
- Création de voyages sur mesure
- Paiement sécurisé
- Suivi des réservations

### Agents
- Création et gestion des forfaits
- Gestion des réservations
- Upload de documents

### Administrateurs
- Gestion complète de la plateforme
- Configuration des commissions
- Gestion des utilisateurs

## Installation

```bash
# Cloner le projet
git clone https://github.com/OumaimaMatii/laravel-travel-app.git

# Installer les dépendances
composer install
npm install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données
php artisan migrate --seed

# Démarrer les serveurs
php artisan serve
npm run dev