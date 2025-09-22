<?php

declare(strict_types=1);

namespace ParcCalanques\Sentiers\Models;

use DateTime;

class Sentier
{
    public function __construct(
        private int $idSentier,
        private string $nom,
        private string $niveauDifficulte,
        private ?string $description,
        private int $idZone,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
        private ?string $nomZone = null
    ) {}

    public function getIdSentier(): int
    {
        return $this->idSentier;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getNiveauDifficulte(): string
    {
        return $this->niveauDifficulte;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIdZone(): int
    {
        return $this->idZone;
    }

    public function getNomZone(): ?string
    {
        return $this->nomZone;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getDifficultyBadgeClass(): string
    {
        return match($this->niveauDifficulte) {
            'facile' => 'bg-green-100 text-green-800',
            'moyen' => 'bg-yellow-100 text-yellow-800',
            'difficile' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getDifficultyIcon(): string
    {
        return match($this->niveauDifficulte) {
            'facile' => '🟢',
            'moyen' => '🟡',
            'difficile' => '🔴',
            default => '⚪'
        };
    }

    public function toArray(): array
    {
        return [
            'id_sentier' => $this->idSentier,
            'nom' => $this->nom,
            'niveau_difficulte' => $this->niveauDifficulte,
            'description' => $this->description,
            'id_zone' => $this->idZone,
            'nom_zone' => $this->nomZone,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'difficulty_badge_class' => $this->getDifficultyBadgeClass(),
            'difficulty_icon' => $this->getDifficultyIcon()
        ];
    }
}


