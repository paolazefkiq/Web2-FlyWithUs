<?php
class User
{
    protected int $id;
    protected string $name;
    protected string $email;
    protected string $username;
    protected string $role;
    protected string $favoriteDestination;

    public function __construct(int $id, string $name, string $email, string $username, string $role, string $favoriteDestination)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->username = $username;
        $this->role = $role;
        $this->favoriteDestination = $favoriteDestination;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getUsername(): string { return $this->username; }
    public function getRole(): string { return $this->role; }
    public function getFavoriteDestination(): string { return $this->favoriteDestination; }

    public function setName(string $name): void
{
    $this->name = $name;
}
?>