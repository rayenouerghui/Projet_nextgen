
<?php

class JeuOwned
{
    private int $user_id;
    private int $jeu_id;
    private int $score;
    private ?string $date_achat = null;

    public function __construct(
        int $user_id,
        int $jeu_id,
        int $score = 0,
        ?string $date_achat = null
    ) {
        $this->user_id    = $user_id;
        $this->jeu_id     = $jeu_id;
        $this->score      = $score;
        $this->date_achat = $date_achat ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getUserId(): int    { return $this->user_id; }
    public function getJeuId(): int     { return $this->jeu_id; }
    public function getScore(): int     { return $this->score; }
    public function getDateAchat(): ?string { return $this->date_achat; }

    // Setters
    public function setScore(int $score): void        { $this->score = $score; }
    public function setDateAchat(?string $date_achat): void { $this->date_achat = $date_achat; }
}