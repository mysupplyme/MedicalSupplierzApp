<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Packages - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #1e40af; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
        .card-header { background: #f8fafc; padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; word-wrap: break-word; }
        .table th { background: #f8fafc; font-weight: bold; }
        .table tr:hover { background: #f9fafb; }
        .table th:nth-child(5), .table td:nth-child(5) { width: 120px; }
        .table th:nth-child(6), .table td:nth-child(6) { width: 120px; }
        .table th:nth-child(8), .table td:nth-child(8) { width: 140px; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.875rem; margin: 0 0.25rem; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 2rem; width: 90%; max-width: 500px; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .form-group textarea { height: 100px; resize: vertical; }
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
        .status-active { background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .status-inactive { background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscription Packages</h1>
        <div>
            <a href="/admin/dashboard" class="btn btn-secondary">Dashboard</a>
            <a href="/admin/subscriptions-management" class="btn btn-secondary">Subscriptions</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Manage Packages</h2>
                <button class="btn btn-primary" onclick="openModal()">Add New Package</button>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>iOS ID</th>
                        <th>Android ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="packagesTable">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">Loading packages...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="packageModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Add New Package</h3>
            <form id="packageForm">
                <input type="hidden" id="packageId">
                
                <div class="form-group">
                    <label for="name_en">Package Name</label>
                    <input type="text" id="name_en" name="name_en" required>
                </div>
                
                <div class="form-group">
                    <label for="description_en">Description</label>
                    <textarea id="description_en" name="description_en"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="period">Duration</label>
                    <input type="number" id="period" name="period" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="type">Duration Type</label>
                    <select id="type" name="type" required>
                        <option value="month">Month(s)</option>
                        <option value="year">Year(s)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cost">Price</label>
                    <input type="number" id="cost" name="cost" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="ios_plan_id">iOS In-App Purchase ID</label>
                    <input type="text" id="ios_plan_id" name="ios_plan_id" placeholder="com.yourapp.subscription.monthly">
                </div>
                
                <div class="form-group">
                    <label for="android_plan_id">Android In-App Purchase ID</label>
                    <input type="text" id="android_plan_id" name="android_plan_id" placeholder="monthly_subscription">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Package</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isEditing = false;
        
        async function loadPackages() {
            try {
                const response = await fetch('/api/admin/packages');
                const result = await response.json();
                
                if (result.success) {
                    displayPackages(result.data);
                }
            } catch (error) {
                console.error('Error loading packages:', error);
            }
        }
        
        function displayPackages(packages) {
            const tbody = document.getElementById('packagesTable');
            
            if (packages.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No packages found.</td></tr>';
                return;
            }
            
            tbody.innerHTML = packages.map(pkg => `
                <tr>
                    <td>${pkg.id}</td>
                    <td>${pkg.name_en}</td>
                    <td>${pkg.period} ${pkg.type}(s)</td>
                    <td>$${pkg.cost}</td>
                    <td>${pkg.ios_plan_id || '-'}</td>
                    <td>${pkg.android_plan_id || '-'}</td>
                    <td><span class="status-${pkg.status ? 'active' : 'inactive'}">${pkg.status ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-primary" onclick="editPackage(${pkg.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deletePackage(${pkg.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function openModal(packageData = null) {
            const modal = document.getElementById('packageModal');
            const form = document.getElementById('packageForm');
            const title = document.getElementById('modalTitle');
            
            if (packageData) {
                isEditing = true;
                title.textContent = 'Edit Package';
                document.getElementById('packageId').value = packageData.id;
                document.getElementById('name_en').value = packageData.name_en;
                document.getElementById('description_en').value = packageData.description_en || '';
                document.getElementById('period').value = packageData.period;
                document.getElementById('type').value = packageData.type;
                document.getElementById('cost').value = packageData.cost;
                document.getElementById('ios_plan_id').value = packageData.ios_plan_id || '';
                document.getElementById('android_plan_id').value = packageData.android_plan_id || '';
                document.getElementById('status').value = packageData.status;
            } else {
                isEditing = false;
                title.textContent = 'Add New Package';
                form.reset();
                document.getElementById('packageId').value = '';
            }
            
            modal.style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('packageModal').style.display = 'none';
        }
        
        async function editPackage(packageId) {
            try {
                const response = await fetch(`/api/admin/packages/${packageId}`);
                const result = await response.json();
                
                if (result.success) {
                    openModal(result.data);
                }
            } catch (error) {
                console.error('Error loading package:', error);
            }
        }
        
        async function deletePackage(packageId) {
            if (!confirm('Are you sure you want to delete this package?')) return;
            
            try {
                const response = await fetch(`/api/admin/packages/${packageId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
                
                const result = await response.json();
                if (result.success) {
                    loadPackages();
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error deleting package:', error);
            }
        }
        
        document.getElementById('packageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.status = parseInt(data.status);
            data.period = parseInt(data.period);
            data.cost = parseInt(data.cost);
            
            const packageId = document.getElementById('packageId').value;
            const url = isEditing ? `/api/admin/packages/${packageId}` : '/api/admin/packages';
            const method = isEditing ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    closeModal();
                    loadPackages();
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error saving package:', error);
            }
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('packageModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Load packages on page load
        loadPackages();
    </script>
</body>
</html>