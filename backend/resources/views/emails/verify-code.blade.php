<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify your email</title>
</head>
<body>
    <h1>Hello {{ $user->name ?: $user->email }},</h1>

    <p>Thank you for registering. Use the verification code below to verify your email address:</p>

    <p style="font-size: 24px; font-weight: bold; letter-spacing: 4px;">{{ $code }}</p>

    <p>If you did not create an account, no further action is required.</p>
</body>
</html>
