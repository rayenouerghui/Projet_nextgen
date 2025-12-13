<?php

class User
{
    private ?int $id = null;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $telephone;
    private string $mdp;
    private string $role;
    private ?string $photo_profil = null;
    private float $credits = 0.00;

    // === NOUVEAUX CHAMPS ===
    private string $statut = 'actif';         // actif, suspendu, banni
    private ?string $last_login = null;       // DATETIME depuis la BDD

    public function __construct(
        string $nom = '',
        string $prenom = '',
        string $email = '',
        string $telephone = '',
        string $mdp = '',
        string $role = 'user',
        ?int $id = null,
        ?string $photo_profil = null,
        float $credits = 0.00,
        string $statut = 'actif',           // nouveau
        ?string $last_login = null          // nouveau
    ) {
        $this->id           = $id;
        $this->nom          = $nom;
        $this->prenom       = $prenom;
        $this->email        = $email;
        $this->telephone    = $telephone;
        $this->mdp          = $mdp;
        $this->role         = $role;
        $this->photo_profil = $photo_profil;
        $this->credits      = $credits;
        $this->statut       = $statut;
        $this->last_login   = $last_login;
    }

    // ==================== GETTERS ====================
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getMdp(): string { return $this->mdp; }
    public function getRole(): string { return $this->role; }
    public function getPhotoProfil(): ?string { return $this->photo_profil; }
    public function getCredits(): float { return $this->credits; }
    public function getStatut(): string { return $this->statut; }
    public function getLastLogin(): ?string { return $this->last_login; }

    // ==================== BONUS ====================
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isUser(): bool { return $this->role === 'user'; }

    // Retourne true si l'utilisateur s'est connecté il y a moins d'1 heure
    public function isOnline(): bool
    {
        if (!$this->last_login) return false;
        return (time() - strtotime($this->last_login)) < 3600; // 3600s = 1h
    }

    // ==================== SETTERS ====================
    public function setId(?int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setMdp(string $mdp): void { $this->mdp = $mdp; }
    public function setRole(string $role): void {
        if (in_array($role, ['admin', 'user'])) {
            $this->role = $role;
        } else {
            throw new InvalidArgumentException("Le rôle doit être 'admin' ou 'user'");
        }
    }
    public function setPhotoProfil(?string $photo_profil): void { $this->photo_profil = $photo_profil; }
    public function setCredits(float $credits): void { $this->credits = $credits; }

    // Nouveaux setters
    public function setStatut(string $statut): void {
        if (in_array($statut, ['actif', 'suspendu', 'banni'])) {
            $this->statut = $statut;
        } else {
            throw new InvalidArgumentException("Statut invalide");
        }
    }
    public function setLastLogin(?string $last_login): void { $this->last_login = $last_login; }
}