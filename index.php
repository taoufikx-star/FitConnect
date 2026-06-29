<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>FitConnect — Abonnements</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: #f4f5f7; color: #1a1a2e; }
  header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
  header h1 { font-size: 1.3rem; font-weight: 600; }
  nav a { color: #a8d5e2; text-decoration: none; margin-left: 1.5rem; font-size: .9rem; }
  nav a:hover { color: #fff; }
  main { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
  .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
  h2 { font-size: 1.1rem; }
  .btn { display: inline-block; padding: .5rem 1.1rem; border-radius: 6px; font-size: .9rem; text-decoration: none; border: none; cursor: pointer; }
  .btn-primary { background: #1a1a2e; color: #fff; }
  .btn-warning { background: #e6a817; color: #fff; }
  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
  th { background: #1a1a2e; color: #fff; text-align: left; padding: .75rem 1rem; font-size: .85rem; font-weight: 500; }
  td { padding: .7rem 1rem; font-size: .9rem; border-bottom: 1px solid #f0f0f0; }
  tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .75rem; }
  .badge-green  { background: #e0f5ea; color: #1e7e44; }
  .badge-red    { background: #fdecea; color: #c0392b; }
  .badge-yellow { background: #fef9e7; color: #b7950b; }
</style>
</head>
<body>
<header>
  <h1>🏋️ FitConnect</h1>
  <nav>
    <a href="?route=dashboard">Tableau de bord</a>
    <a href="?route=adherents">Adhérents</a>
    <a href="?route=abonnements&id=<?= $idAdherent ?? 1 ?>">Abonnements</a>
  </nav>
</header>
<main>
  <div class="top">
    <h2>Abonnements — adhérent #<?= $idAdherent ?? '?' ?></h2>
    <a href="?route=abonnements&action=create" class="btn btn-primary">+ Ajouter</a>
  </div>
  <?php if (empty($abonnements)): ?>
    <p style="color:#888;text-align:center;padding:2rem">Aucun abonnement trouvé.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Type</th><th>Début</th><th>Fin</th><th>Statut</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($abonnements as $ab): ?>
      <tr>
        <td><?= $ab->getIdAbonnement() ?></td>
        <td><?= ucfirst($ab->getType()) ?></td>
        <td><?= $ab->getDateDebut()->format('d/m/Y') ?></td>
        <td><?= $ab->getDateFin()->format('d/m/Y') ?></td>
        <td>
          <?php $s = $ab->getStatut(); ?>
          <span class="badge badge-<?= $s === 'actif' ? 'green' : ($s === 'expiré' ? 'red' : 'yellow') ?>">
            <?= $s ?>
          </span>
        </td>
        <td>
          <?php if ($ab->getStatut() === 'actif'): ?>
          <form method="POST" action="?route=abonnements&action=destroy&id=<?= $ab->getIdAbonnement() ?>" style="display:inline"
                onsubmit="return confirm('Résilier cet abonnement ?');">
            <input type="hidden" name="_method" value="POST">
            <button class="btn btn-warning" style="padding:.3rem .7rem;font-size:.8rem">Résilier</button>
          </form>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</main>
</body>
</html>
