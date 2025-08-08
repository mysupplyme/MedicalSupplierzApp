<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MedicalSupplierz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f8fafc; }
        .container { max-width: 500px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; margin-bottom: 1rem; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .submit-btn { width: 100%; background: #10b981; color: white; padding: 0.75rem; border: none; border-radius: 4px; cursor: pointer; }
        .submit-btn:hover { background: #059669; }
        .message { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; display: none; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Reset Your Password</h1>
            <div id="message" class="message"></div>
            
            <form id="reset-form">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
                
                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');
        
        if (!token || !email) {
            document.getElementById('message').textContent = 'Invalid reset link';
            document.getElementById('message').className = 'message error';
            document.getElementById('message').style.display = 'block';
            document.getElementById('reset-form').style.display = 'none';
        }
        
        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            if (password !== passwordConfirmation) {
                document.getElementById('message').textContent = 'Passwords do not match';
                document.getElementById('message').className = 'message error';
                document.getElementById('message').style.display = 'block';
                return;
            }
            
            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: token,
                        email: email,
                        password: password
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('message').textContent = result.message;
                    document.getElementById('message').className = 'message success';
                    document.getElementById('message').style.display = 'block';
                    document.getElementById('reset-form').style.display = 'none';
                } else {
                    document.getElementById('message').textContent = result.message;
                    document.getElementById('message').className = 'message error';
                    document.getElementById('message').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('message').textContent = 'Network error. Please try again.';
                document.getElementById('message').className = 'message error';
                document.getElementById('message').style.display = 'block';
            }
        });
    </script>
</body>
</html>