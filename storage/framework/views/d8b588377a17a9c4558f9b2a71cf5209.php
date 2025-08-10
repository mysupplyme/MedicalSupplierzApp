<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #1e40af; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #1e40af; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #f8fafc; padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background: #f8fafc; font-weight: bold; }
        .table tr:hover { background: #f9fafb; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.875rem; margin: 0 0.25rem; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
        .status-active { background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .status-cancelled { background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .status-expired { background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin: 2rem 0; }
        .pagination button { padding: 0.5rem 1rem; border: 1px solid #d1d5db; background: white; cursor: pointer; }
        .pagination button.active { background: #1e40af; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscription Management</h1>
        <div>
            <a href="/admin" class="btn btn-secondary">Dashboard</a>
            <a href="/admin/doctors-management" class="btn btn-secondary">Doctors</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-number" id="totalSubs">-</div>
                <div>Total Subscriptions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="activeSubs">-</div>
                <div>Active Subscriptions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="cancelledSubs">-</div>
                <div>Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="monthlyRevenue">-</div>
                <div>Monthly Revenue</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>All Subscriptions</h2>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Doctor</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Platform</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptionsTable">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">Loading subscriptions...</td>
                    </tr>
                </tbody>
            </table>
            
            <div id="pagination" class="pagination"></div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        
        async function loadStats() {
            try {
                const response = await fetch('/api/admin/subscription-stats');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('totalSubs').textContent = result.data.total_subscriptions;
                    document.getElementById('activeSubs').textContent = result.data.active_subscriptions;
                    document.getElementById('cancelledSubs').textContent = result.data.cancelled_subscriptions;
                    document.getElementById('monthlyRevenue').textContent = '$' + result.data.revenue_this_month;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function loadSubscriptions(page = 1) {
            try {
                const response = await fetch(`/api/admin/subscriptions?page=${page}`);
                const result = await response.json();
                
                if (result.success) {
                    displaySubscriptions(result.data);
                    displayPagination(result.pagination);
                }
            } catch (error) {
                console.error('Error loading subscriptions:', error);
            }
        }
        
        function displaySubscriptions(subscriptions) {
            const tbody = document.getElementById('subscriptionsTable');
            
            if (subscriptions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No subscriptions found.</td></tr>';
                return;
            }
            
            tbody.innerHTML = subscriptions.map(sub => `
                <tr>
                    <td>${sub.id}</td>
                    <td>${sub.client ? sub.client.first_name + ' ' + sub.client.last_name : 'N/A'}</td>
                    <td>${sub.subscription ? sub.subscription.name_en : 'N/A'}</td>
                    <td><span class="status-${sub.status}">${sub.status}</span></td>
                    <td>${sub.start_at}</td>
                    <td>${sub.end_at}</td>
                    <td>${sub.platform || 'N/A'}</td>
                    <td>
                        <button class="btn btn-primary" onclick="viewSubscription(${sub.id})">View</button>
                        <button class="btn btn-success" onclick="extendSubscription(${sub.id})">Extend</button>
                        <button class="btn btn-danger" onclick="cancelSubscription(${sub.id})">Cancel</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function displayPagination(pagination) {
            const paginationDiv = document.getElementById('pagination');
            let buttons = '';
            
            for (let i = 1; i <= pagination.total_pages; i++) {
                buttons += `<button class="${i === pagination.current_page ? 'active' : ''}" 
                                   onclick="loadSubscriptions(${i})">${i}</button>`;
            }
            
            paginationDiv.innerHTML = buttons;
        }
        
        async function extendSubscription(subId) {
            const days = prompt('Enter number of days to extend:');
            if (!days || isNaN(days)) return;
            
            try {
                const response = await fetch(`/api/admin/subscriptions/${subId}/extend`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ days: parseInt(days) })
                });
                
                const result = await response.json();
                if (result.success) {
                    loadSubscriptions(currentPage);
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error extending subscription:', error);
            }
        }
        
        async function cancelSubscription(subId) {
            if (!confirm('Are you sure you want to cancel this subscription?')) return;
            
            try {
                const response = await fetch(`/api/admin/subscriptions/${subId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'cancelled' })
                });
                
                const result = await response.json();
                if (result.success) {
                    loadSubscriptions(currentPage);
                    loadStats();
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error cancelling subscription:', error);
            }
        }
        
        function viewSubscription(subId) {
            window.open(`/admin/subscriptions/${subId}`, '_blank');
        }
        
        // Load data on page load
        loadStats();
        loadSubscriptions();
    </script>
</body>
</html><?php /**PATH /var/www/html/events-ecommerce-admin/resources/views/admin/subscriptions-management.blade.php ENDPATH**/ ?>