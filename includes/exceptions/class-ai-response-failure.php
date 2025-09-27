<?php

namespace WcAiReviewResponder\Exceptions;

/**
 * Thrown when the AI provider fails to return a valid response.
 */
class AI_Response_Failure extends \RuntimeException {
    /**
     * @var array<string,mixed>
     */
    private $debugContext = array();

    /**
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @param array<string,mixed> $debugContext Additional debug context
     */
    public function __construct( $message = '', $code = 0, $previous = null, $debugContext = array() ) {
        parent::__construct( $message, $code, $previous );
        $this->debugContext = $debugContext;
    }

    /**
     * @return array<string,mixed>
     */
    public function get_debug_context() {
        return $this->debugContext;
    }
}


