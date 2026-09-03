
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Your Password</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
">

    <div style="
        width: 100%;
        padding: 50px 0;
    ">

        <div style="
            max-width: 520px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        ">

            <!-- Header -->
            <div style="
                padding: 30px;
                text-align: center;
                background-color: #111827;
            ">
                <h1 style="
                    margin: 0;
                    color: #ffffff;
                    font-size: 26px;
                ">
                    Reset Your Password
                </h1>
            </div>

            <!-- Content -->
            <div style="
                padding: 40px 35px;
                text-align: center;
            ">

                <h2 style="
                    margin-top: 0;
                    color: #111827;
                    font-size: 22px;
                ">
                    Forgot your password?
                </h2>

                <p style="
                    color: #6b7280;
                    font-size: 15px;
                    line-height: 1.7;
                    margin-bottom: 30px;
                ">
                    No worries! Please use the verification code below
                    to reset your password.
                </p>

                <!-- OTP -->
                <div style="
                    display: inline-block;
                    padding: 18px 35px;
                    background-color: #f3f4f6;
                    border: 1px solid #e5e7eb;
                    border-radius: 10px;
                    margin-bottom: 25px;
                ">
                    <span style="
                        font-size: 32px;
                        font-weight: bold;
                        letter-spacing: 8px;
                        color: #111827;
                    ">
                        {{ $otp }}
                    </span>
                </div>

                <p style="
                    color: #9ca3af;
                    font-size: 13px;
                    line-height: 1.6;
                ">
                    This verification code will expire in
                    <strong style="color: #374151;">
                        15 minutes
                    </strong>.
                </p>

                <p style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 25px;
                ">
                    If you did not request a password reset, you can safely
                    ignore this email.
                </p>

            </div>

            <!-- Footer -->
            <div style="
                padding: 20px;
                text-align: center;
                background-color: #f9fafb;
                border-top: 1px solid #e5e7eb;
            ">
                <p style="
                    margin: 0;
                    color: #9ca3af;
                    font-size: 12px;
                ">
                    &copy; {{ date('Y') }} My App. All rights reserved.
                </p>
            </div>

        </div>

    </div>

</body>
</html>
