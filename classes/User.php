<?php

class User
{
    protected int $id;
    protected string $name;
    protected string $email;
    protected string $username;
    protected string $role;

    public function __construct(
        int $id,
        string $name,
        string $email,
        string $username,
        string $role
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->username = $username;
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getGreeting(): string
    {
        return 'Mire se erdhe, ' . $this->name . '!';
    }

    public function getDashboardMessage(): string
    {
        return $this->getGreeting();
    }

    public function canSendContactMessage(): bool
    {
        return false;
    }
}