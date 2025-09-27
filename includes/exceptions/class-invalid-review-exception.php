<?php

namespace WcAiReviewResponder\Exceptions;

/**
 * Thrown when a review is missing required data (rating and/or comment).
 */
class Invalid_Review_Exception extends \RuntimeException {
}
