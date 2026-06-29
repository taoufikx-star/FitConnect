<?php

declare(strict_types=1);

namespace FitConnect\Controllers;

use FitConnect\Repositories\AbonnementRepository;
use FitConnect\Repositories\SeanceRepository;
use FitConnect\Services\SeanceService;

class SeanceController
{
    private SeanceService $service;

    public function __construct()
    {
        $this->service = new SeanceService(
            new SeanceRepository(),
            new AbonnementRepository()
        );
    }

    public function index(int $idAdherent): void
    {
        $seances = $this->service->listerParAdherent($idAdherent);
        require __DIR__ . '/../../views/seances/index.php';
    }

    public function store(): void
    {
        try {
            $this->service->enregistrer([
                'id_adherent'      => $_POST['id_adherent']      ?? 0,
                'id_salle'         => $_POST['id_salle']         ?? 0,
                'id_type_activite' => $_POST['id_type_activite'] ?? 0,
                'date_heure'       => $_POST['date_heure']       ?? '',
                'duree_minutes'    => $_POST['duree_minutes']    ?? 0,
                'notes'            => $_POST['notes']            ?? null,
                'equipements'      => $_POST['equipements']      ?? [],
            ]);
            $this->redirect('seances');
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            $error = $e->getMessage();
            require __DIR__ . '/../../views/seances/create.php';
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->service->supprimer($id);
        } catch (\RuntimeException $e) {
            error_log($e->getMessage());
        }
        $this->redirect('seances');
    }

    private function redirect(string $route): void
    {
        header('Location: /index.php?route=' . $route);
        exit;
    }
}
