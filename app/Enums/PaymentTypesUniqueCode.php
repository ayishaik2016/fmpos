<?php
namespace App\Enums;

enum PaymentTypesUniqueCode:string{
    case CASH                     = 'CASH';
    case CHEQUE                   = 'CHEQUE';
    case ONLINE                   = 'ONLINE';
    case BANK                     = 'BANK';
}
