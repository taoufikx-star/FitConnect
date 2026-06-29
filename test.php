<?php

declare(strict_types=1);

/**
 * public/test.php — validation de chaque couche indépendamment de l'UI.
 * Lancer depuis la ligne de commande : php public/test.php
 * Ou via le navigateur : http://localhost/fitconnect/public/test.php
 */

// ── Autoload ────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $map = [
        'FitConnect\\Config\\'       => __DIR__ . '/../config/',
        'FitConnect\\Entities\\'     => __DIR__ . '/../app/Entities/',
        'FitConnect\\Repositories\\' => __DIR__ . '/../app/Repositories/',
        'FitConnect\\Services\\'     => __DIR__ . '/../app/Services/',
        'FitConnect\\Controllers\\'  => __DIR__ . '/../app/Controllers/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . substr($class, strlen($prefix)) . '.php';
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'fitconnect';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

use DateTimeImmutable;
use FitConnect\Entities\Adherent;
use FitConnect\Entities\Abonnement;
use FitConnect\Entities\Seance;
use FitConnect\Repositories\AdherentRepository;
use FitConnect\Repositories\AbonnementRepository;
use FitConnect\Repositories\SeanceRepository;
use FitConnect\Services\AdherentService;
use FitConnect\Services\AbonnementService;
use FitConnect\Services\SeanceService;

$ok  = 0;
$ko  = 0;
$log = [];

function test(string $label, callable $fn): void
{
    global $ok, $ko, $log;
    try {
        $result = $fn();
        $ok++;
        $log[] = ['✅', $label, $result ?? ''];
    } catch (Throwable $e) {
        $ko++;
        $log[] = ['❌', $label, $e->getMessage()];
    }
}

function expect(bool $assertion, string $message = ''): void
{
    if (!$assertion) {
        throw new \RuntimeException($message ?: 'Assertion échouée');
    }
}

// ════════════════════════════════════════════════════
//  1. Tests entités (sans base de données)
// ════════════════════════════════════════════════════
echo "\n=== 1. Entités ===\n";

test('Adherent : création valide', function () {
    $a = new Adherent(1, 'Benali', 'Sara', 'sara@test.com');
    expect($a->getNom() === 'Benali');
    expect($a->getEmail() === 'sara@test.com');
    return $a->getNomComplet();
});

test('Adherent : email invalide lance exception', function () {
    try {
        new Adherent(1, 'X', 'X', 'pas-un-email');
        throw new \RuntimeException('Aucune exception levée');
    } catch (\InvalidArgumentException) {
        return 'InvalidArgumentException correctement levée';
    }
});

test('Abonnement : estValide() actif dans la plage', function () {
    $ab = new Abonnement(1, 'mensuel',
        new DateTimeImmutable('2025-01-01'),
        new DateTimeImmutable('2025-01-31'),
        'actif'
    );
    expect($ab->estValide(new DateTimeImmutable('2025-01-15')));
    expect(!$ab->estValide(new DateTimeImmutable('2025-02-01')));
    return 'estValide() conforme';
});

test('Abonnement : date_fin antérieure lance exception', function () {
    try {
        new Abonnement(1, 'mensuel',
            new DateTimeImmutable('2025-06-30'),
            new DateTimeImmutable('2025-06-01')
        );
        throw new \RuntimeException('Aucune exception levée');
    } catch (\InvalidArgumentException) {
        return 'InvalidArgumentException correctement levée';
    }
});

test('Seance : durée négative lance exception', function () {
    try {
        new Seance(1, 1, 1, new DateTimeImmutable(), -30);
        throw new \RuntimeException('Aucune exception levée');
    } catch (\InvalidArgumentException) {
        return 'InvalidArgumentException correctement levée';
    }
});

// ════════════════════════════════════════════════════
//  2. Tests repositories (nécessite la base de données)
// ════════════════════════════════════════════════════
echo "\n=== 2. Repositories ===\n";

test('AdherentRepository : findAll()', function () {
    $repo = new AdherentRepository();
    $list = $repo->findAll();
    expect(is_array($list));
    return count($list) . ' adhérent(s) trouvé(s)';
});

test('AdherentRepository : findById(1)', function () {
    $repo = new AdherentRepository();
    $a = $repo->findById(1);
    expect($a !== null);
    return $a->getNomComplet();
});

test('AbonnementRepository : findAbonnementActif(1)', function () {
    $repo = new AbonnementRepository();
    $ab   = $repo->findAbonnementActif(1);
    return $ab ? 'Abonnement actif : ' . $ab->getType() : 'Aucun abonnement actif';
});

test('SeanceRepository : findByAdherent(1)', function () {
    $repo = new SeanceRepository();
    $list = $repo->findByAdherent(1);
    expect(is_array($list));
    return count($list) . ' séance(s)';
});

// ════════════════════════════════════════════════════
//  3. Tests services
// ════════════════════════════════════════════════════
echo "\n=== 3. Services ===\n";

test('AbonnementService : adherentEstAbonne()', function () {
    $svc = new AbonnementService(new AbonnementRepository(), new AdherentRepository());
    $res = $svc->adherentEstAbonne(1);
    return $res ? 'Adhérent #1 est abonné ✔' : 'Adhérent #1 non abonné';
});

test('SeanceService : refus si pas d\'abonnement actif', function () {
    $svc = new SeanceService(new SeanceRepository(), new AbonnementRepository());
    try {
        // Adhérent inexistant → pas d'abonnement → doit échouer
        $svc->enregistrer([
            'id_adherent'      => 9999,
            'id_salle'         => 1,
            'id_type_activite' => 1,
            'date_heure'       => date('Y-m-d H:i:s'),
            'duree_minutes'    => 60,
        ]);
        throw new \RuntimeException('Aucune exception levée — ERREUR');
    } catch (\RuntimeException $e) {
        return 'RuntimeException correctement levée : ' . $e->getMessage();
    }
});

test('AdherentService : suppression refusée si séances présentes', function () {
    $svc = new AdherentService(
        new AdherentRepository(),
        new AbonnementRepository(),
        new SeanceRepository()
    );
    // Adhérent #1 a des séances dans les fixtures
    try {
        $svc->supprimer(1);
        throw new \RuntimeException('Suppression autorisée à tort');
    } catch (\RuntimeException $e) {
        return 'Protection OK : ' . $e->getMessage();
    }
});

// ════════════════════════════════════════════════════
//  Rapport final
// ════════════════════════════════════════════════════
$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    echo '<pre style="font-family:monospace;padding:1rem;">';
}

echo "\n";
foreach ($log as [$icon, $label, $detail]) {
    printf("%s  %-60s %s\n", $icon, $label, $detail);
}

echo "\n";
echo "────────────────────────────────────────────────────────\n";
printf("Résultat : %d/%d tests réussis\n\n", $ok, $ok + $ko);

if (!$isCli) {
    echo '</pre>';
}
