<?php

class Admin extends User
{
    public function getDashboardMessage(): string
    {
        return 'Menaxhoni destinacionet, rruget, rezervimet dhe mesazhet nga nje vend.';
    }

    public function canSendContactMessage(): bool
    {
        return false;
    }
}