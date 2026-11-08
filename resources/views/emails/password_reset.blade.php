<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 40px; text-align: center; border-bottom: 3px solid #0f766e;">
                            <h1 style="margin: 0; color: #0f766e; font-size: 28px;">Innobles HRMS</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #333; font-size: 24px;">Password Reset Request</h2>
                            <p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
                                We received a request to reset your password for your Innobles HRMS account. Click the button below to reset your password:
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ $resetLink }}" style="display: inline-block; padding: 14px 32px; background-color: #0f766e; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
                                Or copy and paste this link into your browser:
                            </p>
                            <p style="margin: 0 0 30px 0; color: #0f766e; font-size: 14px; word-break: break-all;">
                                {{ $resetLink }}
                            </p>
                            <p style="margin: 0 0 20px 0; color: #777; font-size: 14px; line-height: 1.6;">
                                <strong>This link will expire in 60 minutes.</strong>
                            </p>
                            <p style="margin: 0 0 20px 0; color: #555; font-size: 16px; line-height: 1.6;">
                                If you did not request a password reset, please ignore this email or contact support if you have questions.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0 0 10px 0; color: #777; font-size: 14px;">
                                &copy; {{ date('Y') }} Innobles HRMS. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #999; font-size: 12px;">
                                This is an automated email. Please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
