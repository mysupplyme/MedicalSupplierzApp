<!DOCTYPE html>
<html>
<head>
    <title>Subscriptions Management - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #007cba; color: white; padding: 20px; margin-bottom: 20px; }
        .content { max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; margin: 2px; border: none; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .status-active { color: green; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }
        .status-expired { color: orange; font-weight: bold; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 5px; flex: 1; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscriptions Management</h1>
        <p>Manage user subscriptions and view statistics</p>
    </div>
    
    <div class="content">
        <div class="stats" id="stats">
            <div class="stat-card">
                <h3>Total Subscriptions</h3>
                <p id="total-subs">Loading...</p>
            </div>
            <div class="stat-card">
                <h3>Active Subscriptions</h3>
                <p id="active-subs">Loading...</p>
            </div>
            <div class="stat-card">
                <h3>Monthly Revenue</h3>
                <p id="revenue">Loading...</p>
            </div>
        </div>

        <div id="subscriptions-list">
            <h2>All Subscriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Platform</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptions-table">
                    <tr>
                        <td colspan="8">Loading subscriptions...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Load subscription stats
        fetch('/api/admin/subscription-stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-subs').textContent = data.data.total || 0;
                    document.getElementById('active-subs').textContent = data.data.active || 0;
                    document.getElementById('revenue').textContent = '$' + (data.data.revenue || 0);
                }
            });

        // Load subscriptions data
        fetch('/api/admin/subscriptions')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('subscriptions-table');
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(sub => `
                        <tr>
                            <td>${sub.id}</td>
                            <td>${sub.client?.name || 'N/A'}</td>
                            <td>${sub.subscription?.name_en || 'N/A'}</td>
                            <td class="status-${sub.status}">${sub.status}</td>
                            <td>${sub.start_at}</td>
                            <td>${sub.end_at}</td>
                            <td>${sub.platform || 'N/A'}</td>
                            <td>
                                <button class="btn btn-success" onclick="updateStatus(${sub.id}, 'active')">Activate</button>
                                <button class="btn btn-danger" onclick="updateStatus(${sub.id}, 'cancelled')">Cancel</button>
                                <button class="btn btn-warning" onclick="extendSubscription(${sub.id})">Extend</button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="8">No subscriptions found</td></tr>';
                }
            })
            .catch(error => {
                document.getElementById('subscriptions-table').innerHTML = '<tr><td colspan="8">Error loading subscriptions</td></tr>';
            });

        function updateStatus(id, status) {
            fetch(`/api/admin/subscriptions/${id}/status`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status })
            }).then(() => location.reload());
        }

        function extendSubscription(id) {
            const days = prompt('Extend by how many days?', '30');
            if (days) {
                fetch(`/api/admin/subscriptions/${id}/extend`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ days: parseInt(days) })
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>