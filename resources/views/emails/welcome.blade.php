<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Medical Supplierz</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c5aa0;">Welcome to Medical Supplierz!</h2>
        
        <p>Dear Dr. {{ $client->first_name }} {{ $client->last_name }},</p>
        
        <p>Thank you for registering with Medical Supplierz. Your account has been successfully created.</p>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Account Details:</h3>
            <p><strong>Email:</strong> {{ $client->email }}</p>
            <p><strong>Specialty:</strong> {{ $client->specialty->name ?? 'Not specified' }}</p>
            <p><strong>Workplace:</strong> {{ $client->workplace ?? 'Not specified' }}</p>
        </div>
        
        <p>You can now:</p>
        <ul>
            <li>Browse medical events and conferences</li>
            <li>Register for CME activities</li>
            <li>Access exclusive medical content</li>
            <li>Connect with other healthcare professionals</li>
        </ul>
        
        <p>If you have any questions, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>
        The Medical Supplierz Team</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666;">
            This email was sent to {{ $client->email }}. If you didn't create this account, please ignore this email.
        </p>
    </div>
</body>
</html>