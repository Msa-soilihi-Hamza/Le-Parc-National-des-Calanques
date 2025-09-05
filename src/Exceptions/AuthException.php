<?php

declare(strict_types=1);

namespace ParcCalanques\Exceptions;

class AuthException extends \Exception
{
    public const INVALID_CREDENTIALS = 'Invalid credentials';
    public const USER_NOT_FOUND = 'User not found';
    public const USER_INACTIVE = 'User account is inactive';
    public const EMAIL_NOT_VERIFIED = 'Email address not verified';
    public const UNAUTHORIZED = 'Unauthorized access';
    public const INSUFFICIENT_PRIVILEGES = 'Insufficient privileges';
    public const SESSION_EXPIRED = 'Session expired';
    public const TOKEN_INVALID = 'Invalid token';
}