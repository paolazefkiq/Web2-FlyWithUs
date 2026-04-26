<?php
class Admin extends User
{
    public function getDashboardMessage(): string
    {
        return 'Ju mund të menaxhoni të dhënat dummy, statistikat dhe përdoruesit statikë.';
    }

    public function canManageUsers(): bool
{
    return true;
}
}