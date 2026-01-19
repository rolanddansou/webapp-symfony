<?php

use App\Feature\Helper\Timezones;
use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    date_default_timezone_set(Timezones::UTC);
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
