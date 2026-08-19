<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\ModulesServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\SiteSettingsServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    ModulesServiceProvider::class,
    RepositoryServiceProvider::class,
    SiteSettingsServiceProvider::class,
];

