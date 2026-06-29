<?php

declare(strict_types=1);

namespace FitConnect\Services;

use DateTimeImmutable;
use FitConnect\Entities\Seance;
use FitConnect\Repositories\AbonnementRepository;
use FitConnect\Repositories\SeanceRepository;
use RuntimeException;

class SeanceService
{
    private SeanceRepository     $seanceRepo;
    private AbonnementRepository $abonnementRepo;

    public function __construct(
        SeanceRepository     $seanceRepo,
        AbonnementRepository $abonnementRepo
    ) {
        $this->seanceRepo     = $seanceRepo;
        $this->abonnementRepo = $abonnementRepo;
    }

    public function listerParAdherent(int $idAdherent): array
    {
        return $this->seanceRepo->findByAdherent($idAdherent);
    }

    public function listerParSalle(int $idSalle): array
    {
        return $this->seanceRepo->findBySalle($idSalle);
    }

    /**
     * Enregistre une séance.
     * Règle de gestion critique : l'abonnement de l'adhérent doit être
     * valide à la date de la séance.
     */
    public function enregistrer(array $data): Seance
    {
        $idAdherent = (int) $data['id_adherent'];
        $dateSeance = (new DateTimeImmutable($data['date_heure']))->format('Y-m-d');

        // Vérification abonnement valide à la date de la séance
        $abonnement = $this->abonnementRepo->findAbonnementActif($idAdherent, $dateSeance);
        if ($abonnement === null) {
            throw new RuntimeException(
                "Impossible d'enregistrer la séance : l'adhérent #$idAdherent "
                . "n'a pas d'abonnement valide à la date du {$dateSeance}."
            );
        }

        $seance = new Seance(
            $idAdherent,
            (int) $data['id_salle'],
            (int) $data['id_type_activite'],
            new DateTimeImmutable($data['date_heure']),
            (int) $data['duree_minutes'],
                  $data['notes'] ?? null
        );

        // Équipements optionnels
        if (!empty($data['equipements']) && is_array($data['equipements'])) {
            $seance->setEquipements($data['equipements']);
        }

        $this->seanceRepo->insert($seance);
        return $seance;
    }

    public function supprimer(int $id): void
    {
        if ($this->seanceRepo->findById($id) === null) {
            throw new RuntimeException("Séance #$id introuvable.");
        }
        $this->seanceRepo->delete($id);
    }
}
