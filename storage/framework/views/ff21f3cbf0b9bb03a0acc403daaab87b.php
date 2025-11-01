<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activate Your Account - Medical Supplierz</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c5aa0;">Activate Your Medical Supplierz Account</h2>
        
        <p>Dear Dr. <?php echo e($client->first_name); ?> <?php echo e($client->last_name); ?>,</p>
        
        <p>Thank you for registering with Medical Supplierz. To complete your registration, please activate your account using the code below:</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <h3 style="margin: 0; color: #2c5aa0;">Activation Code</h3>
            <h1 style="font-size: 32px; color: #007bff; margin: 10px 0; letter-spacing: 3px;"><?php echo e($activation_code); ?></h1>
        </div>
        
        <p>Enter this code in the mobile app to activate your account and start accessing:</p>
        <ul>
            <li>Medical events and conferences</li>
            <li>CME activities and credits</li>
            <li>Exclusive medical content</li>
            <li>Professional networking opportunities</li>
        </ul>
        
        <p><strong>Note:</strong> This activation code will expire in 24 hours for security reasons.</p>
        
        <p>If you didn't create this account, please ignore this email.</p>
        
        <p>Best regards,<br>
        The Medical Supplierz Team</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666;">
            This email was sent to <?php echo e($client->email); ?>. 
        </p>
    </div>
</body>
</html><?php /**PATH /var/www/html/medicalsupplierz.app/resources/views/emails/activation.blade.php ENDPATH**/ ?>