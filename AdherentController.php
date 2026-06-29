<?php

declare(strict_types=1);

namespace FitConnect\Controllers;

use FitConnect\Repositories\AdherentRepository;
use FitConnect\Repositories\AbonnementRepository;
use FitConnect\Repositories\SeanceRepository;
use FitConnect\Services\AdherentService;

/**
 * AdherentController — reçoit les requêtes, délègue au service,
 * prépare les données pour la vue.
 */
class AdherentController
{
    private AdherentService $service;

    public function __construct()
    {
        $this->service = new AdherentService(
            new AdherentRepository(),
            new AbonnementRepository(),
            new SeanceRepository()
        );
    }

    /** Liste tous les adhérents → views/adherents/index.php */
    public function index(): void
    {
        $adherents = $this->service->listerTous();
        require __DIR__ . '/../../views/adherents/index.php';
    }

    /** Formulaire de création → views/adherents/create.php */
    public function create(): void
    {
        require __DIR__ . '/../../views/adherents/create.php';
    }

    /** Traitement du formulaire POST → redirect vers index */
    public function store(): void
    {
        try {
            $this->service->creer([
                'id_salle'  => $_POST['id_salle']  ?? 0,
                'nom'       => $_POST['nom']        ?? '',
                'prenom'    => $_POST['prenom']     ?? '',
                'email'     => $_POST['email']      ?? '',
                'telephone' => $_POST['telephone']  ?? null,
            ]);
            $this->redirect('adherents');
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            $error = $e->getMessage();
            require __DIR__ . '/../../views/adherents/create.php';
        }
    }

    /** Suppression via POST → redirect vers index */
    public function destroy(int $id): void
    {
        try {
            $this->service->supprimer($id);
        } catch (\RuntimeException $e) {
            // En production : flash message
            error_log($e->getMessage());
        }
        $this->redirect('adherents');
    }

    private function redirect(string $route): void
    {
        header('Location: /index.php?route=' . $route);
        exit;
    }
}
