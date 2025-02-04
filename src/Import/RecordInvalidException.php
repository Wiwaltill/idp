<?php

namespace App\Import;

use Exception;
use Throwable;

class RecordInvalidException extends Exception {
    private int $index;

    private string $field;

    public function __construct(int $index, string $field, $code = 0, Throwable $previous = null) {
        parent::__construct(sprintf('Record %d has an invalid field %s', $index, $field), $code, $previous);

        $this->index = $index;
        $this->field = $field;
    }

    /**
     * @return int
     */
    public function getIndex(): int {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getField(): string {
        return $this->field;
    }
}