<!DOCTYPE html>
<html>
<head>
    <title>Medical Professional Login</title>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 40px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .default-creds { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-top: 20px; font-size: 14px; }
        .register-link { text-align: center; margin-top: 20px; }
        .register-link a { color: #007bff; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Medical Professional Login</h1>
        <form id="loginForm">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <div id="error" class="error"></div>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="/doctor-register">Register as Doctor</a></p>
        </div>
        
        <div class="default-creds">
            <strong>Default Admin Account:</strong><br>
            Email: admin@medconf.com<br>
            Password: admin123
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error');
            
            try {
                const response = await axios.post('/api/login', {
                    email: email,
                    password: password
                });
                
                if (response.data.success && response.data.data.token) {
                    localStorage.setItem('auth_token', response.data.data.token);
                    window.location.href = '/dashboard';
                } else {
                    errorDiv.textContent = 'Login failed. Please try again.';
                }
            } catch (error) {
                errorDiv.textContent = error.response?.data?.error?.message || error.response?.data?.message || 'Login failed. Please check your credentials.';
            }
        });
    </script>
</body>
</html><?php /**PATH /var/www/html/medicalsupplierz.app/resources/views/auth/login.blade.php ENDPATH**/ ?>