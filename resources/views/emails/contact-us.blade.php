<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contact Form Submission - MedicalSupplierz</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
        .content { background: #f8fafc; padding: 30px; }
        .field { margin-bottom: 15px; }
        .field strong { color: #1e40af; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Contact Form Submission</h1>
        </div>
        
        <div class="content">
            <div class="field">
                <strong>Name:</strong> {{ $data['name'] }}
            </div>
            
            <div class="field">
                <strong>Email:</strong> {{ $data['email'] }}
            </div>
            
            <div class="field">
                <strong>Phone:</strong> {{ $data['phone'] ?? 'Not provided' }}
            </div>
            
            <div class="field">
                <strong>Subject:</strong> {{ $data['subject'] }}
            </div>
            
            <div class="field">
                <strong>Message:</strong><br>
                {{ $data['message'] }}
            </div>
            
            <div class="field">
                <strong>Submitted at:</strong> {{ now()->format('Y-m-d H:i:s') }}
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 MedicalSupplierz. All rights reserved.</p>
        </div>
    </div>
</body>
</html>