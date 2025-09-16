<?php
namespace App\Exceptions;

use Exception;

class EtgBadRequestException extends Exception {
    public int $codeInt;
    public function __construct(int $codeInt, string $message) {
        parent::__construct($message);
        $this->codeInt = $codeInt;
    }
}