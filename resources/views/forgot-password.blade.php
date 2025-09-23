<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Medical Supplierz</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 40px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .back-link { text-align: center; margin-top: 15px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .error { color: red; margin-top: 10px; }
        .success { color: green; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Forgot Password</h1>
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Enter your email address and we'll send you a link to reset your password.
        </p>
        
        <form id="forgotForm">
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Reset Link</button>
            <div id="message"></div>
        </form>
        
        <div class="back-link">
            <a href="/login">← Back to Login</a>
        </div>
    </div>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch('/api/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.className = 'success';
                    messageDiv.textContent = data.message;
                } else {
                    messageDiv.className = 'error';
                    messageDiv.textContent = data.message;
                }
            } catch (error) {
                messageDiv.className = 'error';
                messageDiv.textContent = 'An error occurred. Please try again.';
            }
        });
    </script>
</body>
</html>