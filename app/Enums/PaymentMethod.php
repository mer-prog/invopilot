<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case Cash = 'cash';
    case Check = 'check';
    case Other = 'other';
}
