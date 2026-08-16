<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \RuntimeException implements ClientExceptionInterface {}
