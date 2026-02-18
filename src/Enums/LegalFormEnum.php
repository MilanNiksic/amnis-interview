<?php

namespace App\Enums;

enum LegalFormEnum: string
{
    case NATURAL_PERSON = 'natural_person';
    case PUBLIC_LIMITED_COMPANY = 'public_limited_company';
    case LIMITED_LIABILITY_COMPANY = 'limited_liability_company';
}
