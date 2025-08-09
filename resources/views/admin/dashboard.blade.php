<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 15px 20px; }
        .container { padding: 20px; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .api-list { background: #f8f9fa; padding: 15px; border-radius: 4px; }
        .api-item { margin: 5px 0; font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Events E-commerce Admin Dashboard</h1>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Welcome to Admin Panel</h2>
            <p>Your Laravel application is running successfully!</p>
            <a href="/api/logout" class="btn">Logout</a>
        </div>
        
        <div class="card">
            <h3>Mobile App APIs</h3>
            <div class="api-list">
                <div class="api-item"><strong>GET</strong> /api/common/specialties</div>
                <div class="api-item"><strong>GET</strong> /api/common/sub-specialties</div>
                <div class="api-item"><strong>GET</strong> /api/common/residencies</div>
                <div class="api-item"><strong>GET</strong> /api/common/nationalities</div>
                <div class="api-item"><strong>GET</strong> /api/common/country-codes</div>
                <div class="api-item"><strong>GET</strong> /api/common/get_categories</div>
                <div class="api-item"><strong>POST</strong> /api/login</div>
                <div class="api-item"><strong>POST</strong> /api/register</div>
                <div class="api-item"><strong>GET</strong> /api/events</div>
            </div>
        </div>
        
        <div class="card">
            <h3>Management</h3>
            <a href="/admin/doctors-management" class="btn">Manage Doctors</a>
            <a href="/admin/subscriptions-management" class="btn">Manage Subscriptions</a>
        </div>
        
        <div class="card">
            <h3>Quick Actions</h3>
            <a href="/api/common/specialties" class="btn">Test API</a>
            <a href="/login" class="btn">Login Page</a>
        </div>
    </div>
</body>
</html>