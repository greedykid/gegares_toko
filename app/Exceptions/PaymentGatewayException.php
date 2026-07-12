<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the payment gateway cannot issue a payment link for an order.
 * OrderService rolls back the half-written order before throwing, so callers
 * only need to show their own error message (redirect back, bot reply, ...).
 */
class PaymentGatewayException extends RuntimeException {}
