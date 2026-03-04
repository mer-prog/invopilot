<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case Usd = 'USD';
    case Eur = 'EUR';
    case Gbp = 'GBP';
    case Jpy = 'JPY';
    case Cad = 'CAD';
    case Aud = 'AUD';
    case Chf = 'CHF';
    case Cny = 'CNY';
    case Krw = 'KRW';
    case Twd = 'TWD';
}
