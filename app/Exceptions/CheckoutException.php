<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the server refuses to build an order because something the client
 * sent no longer holds: an unverifiable shipping rate, a coupon that has since
 * expired, and so on. Nothing is written before it is thrown, so callers only
 * need to surface getMessage() to the customer.
 */
class CheckoutException extends RuntimeException {}
