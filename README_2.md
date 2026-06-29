# FitConnect — Backend PHP OOP

Gestion d'un réseau de 4 salles de sport (adhérents, abonnements, séances) avec **PHP 8.1+**, **MySQL** et **PDO**. Architecture en couches : Entités → Repositories → Services → Controllers → Vues.

---

## Structure du projet

```
fitconnect/
├── app/
│   ├── Controllers/
│   │   ├── AdherentController.php
│   │   ├── AbonnementController.php
│   │   └── SeanceController.php
│   ├── Entities/
│   │   ├── Adherent.php
│   │   ├── Abonnement.php
│   │   └── Seance.php
│   ├── Repositories/
│   │   ├── AdherentRepository.php
│   │   ├── AbonnementRepository.php
│   │   └── SeanceRepository.php
│   └── Services/
│       ├── AdherentService.php
│       ├── AbonnementService.php
│       └── SeanceService.php
├── config/
│   └── Database.php          ← Singleton PDO
├── database/
│   └── fitconnect.sql        ← Script complet (MLD → MySQL + données de test)
├── public/
│   ├── index.php             ← Point d'entrée unique (routeur frontal)
│   └── test.php              ← Tests de chaque couche
└── views/
    ├── dashboard/index.php
    ├── adherents/{index,create}.php
    ├── abonnements/{index,create}.php
    └── seances/index.php
```

---

## Prérequis

- PHP 8.1 ou supérieur (extension PDO + pdo_mysql activées)
- MySQL 8.0+
- Serveur web (Apache/Nginx) ou `php -S localhost:8000 -t public/`

---

## Installation

### 1. Créer la base de données

```bash
mysql -u root -p < database/fitconnect.sql
```

### 2. Configurer la connexion

Modifier les constantes dans `public/index.php` (ou extraire vers un `.env`) :

```php
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'fitconnect';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = 'votre_mot_de_passe';
```

### 3. Lancer le serveur de développement

```bash
php -S localhost:8000 -t public/
```

Puis ouvrir `http://localhost:8000` dans le navigateur.

### 4. Exécuter les tests

```bash
php public/test.php
```

---

## Architecture en couches

```
Requête HTTP
    ↓
public/index.php  (routeur frontal, autoload)
    ↓
Controllers/      (orchestration uniquement — pas de logique métier)
    ↓
Services/         (règles de gestion : validité abonnement, protection suppression…)
    ↓
Repositories/     (CRUD, requêtes paramétrées PDO — pas de logique métier)
    ↓
Entities/         (objets métier avec validation — pas d'accès base)
    ↓
config/Database.php  (singleton PDO)
    ↓
MySQL (fitconnect)
```

---

## Règles de gestion implémentées

| Règle | Classe |
|-------|--------|
| Un adhérent ne peut avoir qu'un abonnement actif à la fois | `AbonnementService::creer()` |
| Une séance ne peut être enregistrée que si l'abonnement est valide | `SeanceService::enregistrer()` |
| Un adhérent ne peut pas être supprimé si des séances lui sont liées | `AdherentService::supprimer()` |
| Un adhérent ne peut pas être supprimé s'il a un abonnement actif | `AdherentService::supprimer()` |
| Email unique par adhérent | `AdherentService::creer()` + contrainte UNIQUE SQL |
| Date de fin > date de début pour un abonnement | `Abonnement::setDates()` + CHECK MySQL |

---

## Modélisation Merise

### MCD → 6 entités
`SALLE`, `ADHERENT`, `ABONNEMENT`, `SEANCE`, `TYPE_ACTIVITE`, `EQUIPEMENT`

### MLD → 7 tables
`salle`, `adherent`, `abonnement`, `seance`, `type_activite`, `equipement`, `seance_equipement`

Toutes les FK sont déclarées avec `ON UPDATE CASCADE ON DELETE RESTRICT` pour protéger l'intégrité référentielle.

---

## Sécurité

- Toutes les requêtes SQL utilisent des **requêtes paramétrées** (PreparedStatements PDO) — aucune interpolation de variable dans le SQL.
- Les sorties HTML sont systématiquement échappées via `htmlspecialchars()`.
- La connexion PDO lève des exceptions (`ERRMODE_EXCEPTION`) capturées centralement.
- Les détails d'erreur sont logués côté serveur (`error_log`), jamais exposés à l'utilisateur.

---

## Auteur

Projet pédagogique — DevAcademy × FitConnect
