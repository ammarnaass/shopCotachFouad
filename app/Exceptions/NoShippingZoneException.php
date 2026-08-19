<?php

namespace App\Exceptions;

use Exception;

class NoShippingZoneException extends Exception
{
    protected string $city;
    protected ?string $countryCode;

    public function __construct(string $city, ?string $countryCode = null)
    {
        $this->city        = $city;
        $this->countryCode = $countryCode;

        parent::__construct(
            'لا توجد منطقة شحن تغطي: ' . $city . ', ' . ($countryCode ?? 'غير محدد')
        );
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }
}
