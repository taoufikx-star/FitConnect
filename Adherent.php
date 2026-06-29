<?php

declare(strict_types=1);

namespace FitConnect\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Entité Adhérent — reflète fidèlement la table `adherent` du MLD.
 * Attributs privés, accesseurs publics, validation en écriture.
 */
class Adherent
{
    private ?int              $idAdherent;
    private int               $idSalle;
    private string            $nom;
    private string            $prenom;
    private string            $email;
    private ?string           $telephone;
    private DateTimeImmutable $dateInscription;

    public function __construct(
        int               $idSalle,
        string            $nom,
        string            $prenom,
        string            $email,
        ?string           $telephone       = null,
        ?DateTimeImmutable $dateInscription = null,
        ?int              $idAdherent      = null
    ) {
        $this->idAdherent      = $idAdherent;
        $this->idSalle         = $idSalle;
        $this->dateInscription = $dateInscription ?? new DateTimeImmutable('today');

        $this->setNom($nom);
        $this->setPrenom($prenom);
        $this->setEmail($email);
        $this->telephone = $telephone;
    }

    // ── Getters ──────────────────────────────────────────────

    public function getIdAdherent(): ?int              { return $this->idAdherent; }
    public function getIdSalle(): int                  { return $this->idSalle; }
    public function getNom(): string                   { return $this->nom; }
    public function getPrenom(): string                { return $this->prenom; }
    public function getEmail(): string                 { return $this->email; }
    public function getTelephone(): ?string            { return $this->telephone; }
    public function getDateInscription(): DateTimeImmutable { return $this->dateInscription; }
    public function getNomComplet(): string            { return $this->prenom . ' ' . $this->nom; }

    // ── Setters avec validation ───────────────────────────────

    public function setIdAdherent(int $id): void
    {
        $this->idAdherent = $id;
    }

    public function setNom(string $nom): void
    {
        $nom = trim($nom);
        if ($nom === '') {
            throw new InvalidArgumentException('Le nom ne peut pas être vide.');
        }
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void
    {
        $prenom = trim($prenom);
        if ($prenom === '') {
            throw new InvalidArgumentException('Le prénom ne peut pas être vide.');
        }
        $this->prenom = $prenom;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'adresse email « $email » est invalide.");
        }
        $this->email = $email;
    }

    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function setIdSalle(int $idSalle): void
    {
        $this->idSalle = $idSalle;
    }

    // ── Sérialisation ────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id_adherent'      => $this->idAdherent,
            'id_salle'         => $this->idSalle,
            'nom'              => $this->nom,
            'prenom'           => $this->prenom,
            'email'            => $this->email,
            'telephone'        => $this->telephone,
            'date_inscription' => $this->dateInscription->format('Y-m-d'),
        ];
    }
}
