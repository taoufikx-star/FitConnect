<?php

declare(strict_types=1);

namespace FitConnect\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Entité Abonnement — reflète la table `abonnement` du MLD.
 */
class Abonnement
{
    public const TYPES  = ['mensuel', 'trimestriel', 'annuel'];
    public const STATUTS = ['actif', 'expiré', 'suspendu'];

    private ?int              $idAbonnement;
    private int               $idAdherent;
    private string            $type;
    private DateTimeImmutable $dateDebut;
    private DateTimeImmutable $dateFin;
    private string            $statut;

    public function __construct(
        int               $idAdherent,
        string            $type,
        DateTimeImmutable $dateDebut,
        DateTimeImmutable $dateFin,
        string            $statut       = 'actif',
        ?int              $idAbonnement = null
    ) {
        $this->idAbonnement = $idAbonnement;
        $this->idAdherent   = $idAdherent;
        $this->setType($type);
        $this->setStatut($statut);
        $this->setDates($dateDebut, $dateFin);
    }

    // ── Getters ──────────────────────────────────────────────

    public function getIdAbonnement(): ?int              { return $this->idAbonnement; }
    public function getIdAdherent(): int                 { return $this->idAdherent; }
    public function getType(): string                    { return $this->type; }
    public function getDateDebut(): DateTimeImmutable    { return $this->dateDebut; }
    public function getDateFin(): DateTimeImmutable      { return $this->dateFin; }
    public function getStatut(): string                  { return $this->statut; }

    /**
     * Vérifie si l'abonnement est valide à une date donnée.
     * Règle de gestion : statut = actif ET date dans la plage.
     */
    public function estValide(?DateTimeImmutable $date = null): bool
    {
        $date = $date ?? new DateTimeImmutable('today');

        return $this->statut === 'actif'
            && $date >= $this->dateDebut
            && $date <= $this->dateFin;
    }

    // ── Setters avec validation ───────────────────────────────

    public function setIdAbonnement(int $id): void      { $this->idAbonnement = $id; }

    public function setType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Type d'abonnement invalide : « $type ».");
        }
        $this->type = $type;
    }

    public function setStatut(string $statut): void
    {
        if (!in_array($statut, self::STATUTS, true)) {
            throw new InvalidArgumentException("Statut d'abonnement invalide : « $statut ».");
        }
        $this->statut = $statut;
    }

    public function setDates(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($fin <= $debut) {
            throw new InvalidArgumentException('La date de fin doit être postérieure à la date de début.');
        }
        $this->dateDebut = $debut;
        $this->dateFin   = $fin;
    }

    // ── Sérialisation ────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id_abonnement' => $this->idAbonnement,
            'id_adherent'   => $this->idAdherent,
            'type'          => $this->type,
            'date_debut'    => $this->dateDebut->format('Y-m-d'),
            'date_fin'      => $this->dateFin->format('Y-m-d'),
            'statut'        => $this->statut,
        ];
    }
}
