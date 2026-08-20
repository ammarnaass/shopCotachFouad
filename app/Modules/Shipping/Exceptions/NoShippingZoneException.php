<?php

namespace App\Modules\Shipping\Exceptions;

use App\Exceptions\NoShippingZoneException as BaseNoShippingZoneException;

/**
 * تُرمى دائمًا عندما لا توجد أي منطقة شحن (لا مطابقة، ولا حتى منطقة افتراضية).
 * ممنوع نهائيًا أن يُستبدل هذا الاستثناء بـ return 0 أو أي شحن ضمني.
 */
class NoShippingZoneException extends BaseNoShippingZoneException
{
    public function __construct(
        string $city,
        ?string $countryCode = null,
        protected ?string $stateName = null,
    ) {
        parent::__construct($city, $countryCode);
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function context(): array
    {
        return [
            'city' => $this->city,
            'country_code' => $this->countryCode,
            'state_name' => $this->stateName,
        ];
    }
}
