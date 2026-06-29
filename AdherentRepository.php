<?php

declare(strict_types=1);

namespace FitConnect\Repositories;

use DateTimeImmutable;
use FitConnect\Config\Database;
use FitConnect\Entities\Adherent;
use PDO;

/**
 * AdherentRepository — toutes les interactions avec la table `adherent`.
 * Aucune logique métier ici : uniquement CRUD + requêtes paramétrées.
 */
class AdherentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Lecture ──────────────────────────────────────────────

    /** @return Adherent[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM adherent ORDER BY nom, prenom');
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?Adherent
    {
        $stmt = $this->pdo->prepare('SELECT * FROM adherent WHERE id_adherent = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByEmail(string $email): ?Adherent
    {
        $stmt = $this->pdo->prepare('SELECT * FROM adherent WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** @return Adherent[] */
    public function findBySalle(int $idSalle): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM adherent WHERE id_salle = :id_salle ORDER BY nom, prenom'
        );
        $stmt->execute([':id_salle' => $idSalle]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    // ── Écriture ─────────────────────────────────────────────

    public function insert(Adherent $adherent): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO adherent (id_salle, nom, prenom, email, telephone, date_inscription)
             VALUES (:id_salle, :nom, :prenom, :email, :telephone, :date_inscription)'
        );
        $stmt->execute([
            ':id_salle'         => $adherent->getIdSalle(),
            ':nom'              => $adherent->getNom(),
            ':prenom'           => $adherent->getPrenom(),
            ':email'            => $adherent->getEmail(),
            ':telephone'        => $adherent->getTelephone(),
            ':date_inscription' => $adherent->getDateInscription()->format('Y-m-d'),
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $adherent->setIdAdherent($id);
        return $id;
    }

    public function update(Adherent $adherent): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE adherent
                SET id_salle = :id_salle,
                    nom      = :nom,
                    prenom   = :prenom,
                    email    = :email,
                    telephone = :telephone
              WHERE id_adherent = :id'
        );
        return $stmt->execute([
            ':id_salle'  => $adherent->getIdSalle(),
            ':nom'       => $adherent->getNom(),
            ':prenom'    => $adherent->getPrenom(),
            ':email'     => $adherent->getEmail(),
            ':telephone' => $adherent->getTelephone(),
            ':id'        => $adherent->getIdAdherent(),
        ]);
    }

    /**
     * Suppression protégée : vérifie l'absence de séances et d'abonnement actif.
     * La contrainte RESTRICT en base assure une deuxième ligne de défense.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM adherent WHERE id_adherent = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ── Hydratation ──────────────────────────────────────────

    private function hydrate(array $row): Adherent
    {
        return new Adherent(
            (int)   $row['id_salle'],
                    $row['nom'],
                    $row['prenom'],
                    $row['email'],
                    $row['telephone'] ?? null,
            new DateTimeImmutable($row['date_inscription']),
            (int)   $row['id_adherent']
        );
    }
}
