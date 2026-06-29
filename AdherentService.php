<?php

declare(strict_types=1);

namespace FitConnect\Services;

use FitConnect\Entities\Adherent;
use FitConnect\Repositories\AbonnementRepository;
use FitConnect\Repositories\AdherentRepository;
use FitConnect\Repositories\SeanceRepository;
use RuntimeException;

/**
 * AdherentService — règles de gestion relatives aux adhérents.
 * Indépendant du transport (HTTP, CLI, tests).
 */
class AdherentService
{
    private AdherentRepository   $adherentRepo;
    private AbonnementRepository $abonnementRepo;
    private SeanceRepository     $seanceRepo;

    public function __construct(
        AdherentRepository   $adherentRepo,
        AbonnementRepository $abonnementRepo,
        SeanceRepository     $seanceRepo
    ) {
        $this->adherentRepo   = $adherentRepo;
        $this->abonnementRepo = $abonnementRepo;
        $this->seanceRepo     = $seanceRepo;
    }

    /** @return Adherent[] */
    public function listerTous(): array
    {
        return $this->adherentRepo->findAll();
    }

    public function trouverParId(int $id): Adherent
    {
        $adherent = $this->adherentRepo->findById($id);
        if ($adherent === null) {
            throw new RuntimeException("Adhérent #$id introuvable.");
        }
        return $adherent;
    }

    public function creer(array $data): Adherent
    {
        // Unicité email
        if ($this->adherentRepo->findByEmail($data['email']) !== null) {
            throw new RuntimeException("L'email « {$data['email']} » est déjà utilisé.");
        }

        $adherent = new Adherent(
            (int) $data['id_salle'],
                  $data['nom'],
                  $data['prenom'],
                  $data['email'],
                  $data['telephone'] ?? null
        );

        $this->adherentRepo->insert($adherent);
        return $adherent;
    }

    public function modifier(int $id, array $data): Adherent
    {
        $adherent = $this->trouverParId($id);

        // Vérifier unicité email si modifié
        if ($data['email'] !== $adherent->getEmail()) {
            $existant = $this->adherentRepo->findByEmail($data['email']);
            if ($existant !== null && $existant->getIdAdherent() !== $id) {
                throw new RuntimeException("L'email « {$data['email']} » est déjà utilisé.");
            }
        }

        $adherent->setNom($data['nom']);
        $adherent->setPrenom($data['prenom']);
        $adherent->setEmail($data['email']);
        $adherent->setTelephone($data['telephone'] ?? null);
        $adherent->setIdSalle((int) $data['id_salle']);

        $this->adherentRepo->update($adherent);
        return $adherent;
    }

    /**
     * Suppression protégée.
     * Règle : impossible si séances enregistrées ou abonnement en cours.
     */
    public function supprimer(int $id): void
    {
        $this->trouverParId($id); // existence

        if ($this->seanceRepo->countByAdherent($id) > 0) {
            throw new RuntimeException(
                "Impossible de supprimer cet adhérent : des séances lui sont associées."
            );
        }

        $abonnementActif = $this->abonnementRepo->findAbonnementActif($id);
        if ($abonnementActif !== null) {
            throw new RuntimeException(
                "Impossible de supprimer cet adhérent : il possède un abonnement actif."
            );
        }

        $this->adherentRepo->delete($id);
    }
}
