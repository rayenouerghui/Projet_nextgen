<?php

class Historique
{
    private ?int $id_historique = null;
    private int $id_user;
    private string $type_action;
    private ?string $description = null;
    private string $date_action;

    public function __construct(
        int $id_user = 0,
        string $type_action = '',
        ?string $description = null,
        ?int $id_historique = null,
        ?string $date_action = null
    ) {
        $this->id_user = $id_user;
        $this->type_action = $type_action;
        $this->description = $description;
        $this->id_historique = $id_historique;
        $this->date_action = $date_action ?? date('Y-m-d H:i:s');
    }

    // GETTERS
    public function getIdHistorique(): ?int { return $this->id_historique; }
    public function getIdUser(): int { return $this->id_user; }
    public function getTypeAction(): string { return $this->type_action; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateAction(): string { return $this->date_action; }

    // SETTERS
    public function setIdHistorique(?int $id): void { $this->id_historique = $id; }
    public function setIdUser(int $id_user): void { $this->id_user = $id_user; }
    public function setTypeAction(string $type): void { $this->type_action = $type; }
    public function setDescription(?string $desc): void { $this->description = $desc; }
    public function setDateAction(string $date): void { $this->date_action = $date; }
}