<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Medical Supplierz</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a2e; margin: 0; padding: 40px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #dc3545; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #c82333; }
        .doctor-link { text-align: center; margin-top: 20px; }
        .doctor-link a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Login</h1>
        <form id="adminLoginForm">
            <div class="form-group">
                <label>Admin Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Admin Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Admin Login</button>
        </form>
        
        <div class="doctor-link">
            <p><a href="/login">Doctor Login →</a></p>
        </div>
        
        <div id="error" style="color: red; text-align: center; margin-top: 10px;"></div>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const errorDiv = document.getElementById('error');
            
            try {
                const response = await fetch('/api/admin/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('admin_token', data.data.token);
                    window.location.href = '/admin/dashboard';
                } else {
                    errorDiv.textContent = data.message || 'Login failed';
                }
            } catch (error) {
                errorDiv.textContent = 'Login failed. Please try again.';
            }
        });
    </script>
</body>
</html>