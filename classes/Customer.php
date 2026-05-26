<?php

class Customer extends User
{
    public function getDashboardMessage(): string
    {
        return 'Shikoni rezervimet dhe mesazhet e ruajtura ne llogarine tuaj.';
    }

    public function canSendContactMessage(): bool
    {
        return true;
    }
}