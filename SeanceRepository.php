<?php

declare(strict_types=1);

namespace FitConnect\Repositories;

use DateTimeImmutable;
use FitConnect\Config\Database;
use FitConnect\Entities\Seance;
use PDO;

class SeanceRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────

    public function findById(int $id): ?Seance
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seance WHERE id_seance = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $seance = $this->hydrate($row);
        $seance->setEquipements($this->fetchEquipements($id));
        return $seance;
    }

    /** @return Seance[] */
    public function findByAdherent(int $idAdherent): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seance WHERE id_adherent = :id ORDER BY date_heure DESC'
        );
        $stmt->execute([':id' => $idAdherent]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** @return Seance[] */
    public function findBySalle(int $idSalle): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seance WHERE id_salle = :id ORDER BY date_heure DESC'
        );
        $stmt->execute([':id' => $idSalle]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** Compte les séances d'un adhérent (pour la règle de suppression). */
    public function countByAdherent(int $idAdherent): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM seance WHERE id_adherent = :id'
        );
        $stmt->execute([':id' => $idAdherent]);
        return (int) $stmt->fetchColumn();
    }

    // ── Écriture ─────────────────────────────────────────────

    public function insert(Seance $seance): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO seance (id_adherent, id_salle, id_type_activite, date_heure, duree_minutes, notes)
                 VALUES (:id_adherent, :id_salle, :id_type_activite, :date_heure, :duree_minutes, :notes)'
            );
            $stmt->execute([
                ':id_adherent'      => $seance->getIdAdherent(),
                ':id_salle'         => $seance->getIdSalle(),
                ':id_type_activite' => $seance->getIdTypeActivite(),
                ':date_heure'       => $seance->getDateHeure()->format('Y-m-d H:i:s'),
                ':duree_minutes'    => $seance->getDureeMinutes(),
                ':notes'            => $seance->getNotes(),
            ]);

            $id = (int) $this->pdo->lastInsertId();
            $seance->setIdSeance($id);

            // Liaison équipements
            foreach ($seance->getEquipements() as $idEquipement) {
                $this->insertEquipement($id, $idEquipement);
            }

            $this->pdo->commit();
            return $id;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        // Les entrées seance_equipement sont supprimées par CASCADE
        $stmt = $this->pdo->prepare('DELETE FROM seance WHERE id_seance = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ── Équipements ──────────────────────────────────────────

    private function insertEquipement(int $idSeance, int $idEquipement): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO seance_equipement (id_seance, id_equipement) VALUES (:s, :e)'
        );
        $stmt->execute([':s' => $idSeance, ':e' => $idEquipement]);
    }

    /** @return int[] */
    private function fetchEquipements(int $idSeance): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_equipement FROM seance_equipement WHERE id_seance = :id'
        );
        $stmt->execute([':id' => $idSeance]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Hydratation ──────────────────────────────────────────

    private function hydrate(array $row): Seance
    {
        return new Seance(
            (int) $row['id_adherent'],
            (int) $row['id_salle'],
            (int) $row['id_type_activite'],
            new DateTimeImmutable($row['date_heure']),
            (int) $row['duree_minutes'],
                  $row['notes'] ?? null,
            (int) $row['id_seance']
        );
    }
}
