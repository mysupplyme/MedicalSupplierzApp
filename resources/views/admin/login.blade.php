<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MedicalSupplierz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .logo { text-align: center; margin-bottom: 2rem; }
        .logo h1 { color: #1e40af; font-size: 1.8rem; margin-bottom: 0.5rem; }
        .logo p { color: #6b7280; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #374151; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1); }
        .login-btn { width: 100%; background: #1e40af; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; transition: background 0.3s; }
        .login-btn:hover { background: #1d4ed8; }
        .error-message { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; display: none; }
        .back-link { text-align: center; margin-top: 1rem; }
        .back-link a { color: #1e40af; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Admin Portal</h1>
            <p>MedicalSupplierz Administration</p>
        </div>
        
        <div id="errorMessage" class="error-message"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Login to Admin Panel</button>
        </form>
        
        <div class="back-link">
            <a href="/">← Back to Website</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            
            // Simple admin check (replace with proper authentication)
            if (email === 'admin@medicalsupplierz.com' && password === 'admin123') {
                // Set admin session (simplified)
                localStorage.setItem('admin_logged_in', 'true');
                window.location.href = '/admin/dashboard';
            } else {
                errorDiv.textContent = 'Invalid email or password';
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>