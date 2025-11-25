<?php

namespace App\Exceptions;

use Exception;

class EtgApiException extends Exception
{
    public function __construct(
        public int $codeEtg,
        public string $messageEtg,
        public int $httpStatus = 400
    ) {
        parent::__construct($messageEtg, $codeEtg);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function toArray(): array
    {
        return [
            'code'    => $this->codeEtg,
            'message' => $this->messageEtg,
        ];
    }
}