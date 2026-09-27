<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One Time Password Length
    |--------------------------------------------------------------------------
    |
    | Number of digits every generated code contains. Codes are always
    | zero padded so a code such as 4213 is delivered as "004213".
    |
    */

    'length' => (int) env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Lifetime Per OTP Type
    |--------------------------------------------------------------------------
    |
    | Minutes a code stays valid, keyed by purpose. Every issuance replaces
    | any earlier unconsumed code of the same type for that user, and the
    | value is also what the verification mail tells the recipient.
    |
    */

    'ttl' => [
        'email_verify' => (int) env('OTP_EMAIL_VERIFY_TTL', 10),
        'password_reset' => (int) env('OTP_PASSWORD_RESET_TTL', 15),
        'account_delete' => (int) env('OTP_ACCOUNT_DELETE_TTL', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Requests allowed per minute when a code is mailed out, keyed by the
    | target email address and by client IP, plus the attempt limit applied
    | when a code is submitted back for verification.
    |
    */

    'throttle' => [
        'send' => (int) env('OTP_SEND_THROTTLE', 3),
        'send_per_ip' => (int) env('OTP_SEND_THROTTLE_PER_IP', 10),
        'verify' => (int) env('OTP_VERIFY_THROTTLE', 10),
    ],

];
