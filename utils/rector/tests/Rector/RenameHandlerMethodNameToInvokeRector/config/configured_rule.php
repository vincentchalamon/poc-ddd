<?php

declare(strict_types=1);

use App\Utils\Rector\Rector\RenameHandlerMethodNameToInvokeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RenameHandlerMethodNameToInvokeRector::class);
};
