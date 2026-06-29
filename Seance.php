<?php

declare(strict_types=1);

namespace FitConnect\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Entité Séance — reflète la table `seance` du MLD.
 */
class Seance
{
    private ?int              $idSeance;
    private int               $idAdherent;
    private int               $idSalle;
    private int               $idTypeActivite;
    private DateTimeImmutable $dateHeure;
    private int               $dureeMinutes;
    private ?string           $notes;
    /** @var int[] Liste des id_equipement utilisés (table d'association) */
    private array             $equipements = [];

    public function __construct(
        int               $idAdherent,
        int               $idSalle,
        int               $idTypeActivite,
        DateTimeImmutable $dateHeure,
        int               $dureeMinutes,
        ?string           $notes   = null,
        ?int              $idSeance = null
    ) {
        $this->idSeance       = $idSeance;
        $this->idAdherent     = $idAdherent;
        $this->idSalle        = $idSalle;
        $this->idTypeActivite = $idTypeActivite;
        $this->dateHeure      = $dateHeure;
        $this->notes          = $notes;
        $this->setDureeMinutes($dureeMinutes);
    }

    // ── Getters ──────────────────────────────────────────────

    public function getIdSeance(): ?int              { return $this->idSeance; }
    public function getIdAdherent(): int             { return $this->idAdherent; }
    public function getIdSalle(): int                { return $this->idSalle; }
    public function getIdTypeActivite(): int         { return $this->idTypeActivite; }
    public function getDateHeure(): DateTimeImmutable { return $this->dateHeure; }
    public function getDureeMinutes(): int           { return $this->dureeMinutes; }
    public function getNotes(): ?string              { return $this->notes; }
    /** @return int[] */
    public function getEquipements(): array          { return $this->equipements; }

    // ── Setters avec validation ───────────────────────────────

    public function setIdSeance(int $id): void       { $this->idSeance = $id; }

    public function setDureeMinutes(int $duree): void
    {
        if ($duree <= 0) {
            throw new InvalidArgumentException('La durée doit être un entier positif (en minutes).');
        }
        $this->dureeMinutes = $duree;
    }

    public function setNotes(?string $notes): void   { $this->notes = $notes; }

    /** @param int[] $equipements */
    public function setEquipements(array $equipements): void
    {
        $this->equipements = array_map('intval', $equipements);
    }

    public function addEquipement(int $idEquipement): void
    {
        if (!in_array($idEquipement, $this->equipements, true)) {
            $this->equipements[] = $idEquipement;
        }
    }

    // ── Sérialisation ────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id_seance'        => $this->idSeance,
            'id_adherent'      => $this->idAdherent,
            'id_salle'         => $this->idSalle,
            'id_type_activite' => $this->idTypeActivite,
            'date_heure'       => $this->dateHeure->format('Y-m-d H:i:s'),
            'duree_minutes'    => $this->dureeMinutes,
            'notes'            => $this->notes,
            'equipements'      => $this->equipements,
        ];
    }
}
