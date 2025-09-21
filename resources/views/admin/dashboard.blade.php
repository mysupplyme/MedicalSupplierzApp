<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 20px; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <button class="btn btn-logout" onclick="logout()">Logout</button>
    </div>
    
    <div class="container">
        <div class="card">
            <h3>Management</h3>
            <a href="/admin/doctors-management" class="btn">Manage Doctors</a>
            <a href="/admin/subscriptions-management" class="btn">Manage Subscriptions</a>
            <a href="/admin/subscription-packages" class="btn">Manage Packages</a>
        </div>
    </div>

    <script>
        function logout() {
            localStorage.removeItem('admin_token');
            window.location.href = '/admin/login';
        }
    </script>
</body>
</html>