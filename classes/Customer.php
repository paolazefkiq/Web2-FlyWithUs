<?php
class Customer extends User
{
    public function getDashboardMessage(): string
    {
        return 'Ju mund të shihni preferencat, rezervimet dummy dhe ofertat personale.';
    }

    public function canManageUsers(): bool
{
    return false;
}
}
?>
