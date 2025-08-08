<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset - MedicalSupplierz</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
        .content { background: #f8fafc; padding: 30px; }
        .button { background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>
        
        <div class="content">
            <p>Hello Dr. {{ $client->first_name }} {{ $client->last_name }},</p>
            
            <p>We received a request to reset your password for your MedicalSupplierz account.</p>
            
            <p>Click the button below to reset your password:</p>
            
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            
            <p>Or copy and paste this link in your browser:</p>
            <p>{{ $resetUrl }}</p>
            
            <p>This link will expire in 1 hour for security reasons.</p>
            
            <p>If you didn't request this password reset, please ignore this email.</p>
            
            <p>Best regards,<br>MedicalSupplierz Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 MedicalSupplierz. All rights reserved.</p>
            <p>Block7, Street 72, Al-Ajeel Com Center, First Floor, Fahaheel, Ahmadi, Kuwait</p>
        </div>
    </div>
</body>
</html>