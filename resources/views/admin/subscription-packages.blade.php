<!DOCTYPE html>
<html>
<head>
    <title>Subscription Packages - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #007cba; color: white; padding: 20px; margin-bottom: 20px; }
        .content { max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; margin: 2px; border: none; cursor: pointer; }
        .btn-primary { background: #007cba; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 50px auto; padding: 20px; width: 80%; max-width: 500px; }
        .close { float: right; font-size: 28px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscription Packages</h1>
        <p>Manage subscription plans and pricing</p>
    </div>
    
    <div class="content">
        <button class="btn btn-success" onclick="openModal()">Add New Package</button>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Type</th>
                    <th>iOS Plan ID</th>
                    <th>Android Plan ID</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="packages-table">
                <tr>
                    <td colspan="9">Loading packages...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="packageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modal-title">Add New Package</h2>
            <form id="package-form">
                <div class="form-group">
                    <label>Name (English):</label>
                    <input type="text" id="name_en" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea id="description_en"></textarea>
                </div>
                <div class="form-group">
                    <label>Price:</label>
                    <input type="number" step="0.01" id="cost" required>
                </div>
                <div class="form-group">
                    <label>Duration:</label>
                    <input type="number" id="period" required>
                </div>
                <div class="form-group">
                    <label>Type:</label>
                    <select id="type" required>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>iOS Plan ID:</label>
                    <input type="text" id="ios_plan_id">
                </div>
                <div class="form-group">
                    <label>Android Plan ID:</label>
                    <input type="text" id="android_plan_id">
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select id="status" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Package</button>
            </form>
        </div>
    </div>

    <script>
        let editingId = null;

        // Load packages
        function loadPackages() {
            fetch('/api/admin/packages')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('packages-table');
                    if (data.success && data.data.length > 0) {
                        tbody.innerHTML = data.data.map(pkg => `
                            <tr>
                                <td>${pkg.id}</td>
                                <td>${pkg.name_en}</td>
                                <td>$${pkg.cost}</td>
                                <td>${pkg.period}</td>
                                <td>${pkg.type}</td>
                                <td>${pkg.ios_plan_id || 'N/A'}</td>
                                <td>${pkg.android_plan_id || 'N/A'}</td>
                                <td>${pkg.status}</td>
                                <td>
                                    <button class="btn btn-warning" onclick="editPackage(${pkg.id})">Edit</button>
                                    <button class="btn btn-danger" onclick="deletePackage(${pkg.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="9">No packages found</td></tr>';
                    }
                });
        }

        function openModal() {
            document.getElementById('packageModal').style.display = 'block';
            document.getElementById('modal-title').textContent = 'Add New Package';
            document.getElementById('package-form').reset();
            editingId = null;
        }

        function closeModal() {
            document.getElementById('packageModal').style.display = 'none';
        }

        function editPackage(id) {
            fetch(`/api/admin/packages/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const pkg = data.data;
                        document.getElementById('name_en').value = pkg.name_en;
                        document.getElementById('description_en').value = pkg.description_en || '';
                        document.getElementById('cost').value = pkg.cost;
                        document.getElementById('period').value = pkg.period;
                        document.getElementById('type').value = pkg.type;
                        document.getElementById('ios_plan_id').value = pkg.ios_plan_id || '';
                        document.getElementById('android_plan_id').value = pkg.android_plan_id || '';
                        document.getElementById('status').value = pkg.status ? '1' : '0';
                        
                        document.getElementById('modal-title').textContent = 'Edit Package';
                        document.getElementById('packageModal').style.display = 'block';
                        editingId = id;
                    }
                });
        }

        function deletePackage(id) {
            if (confirm('Are you sure you want to delete this package?')) {
                fetch(`/api/admin/packages/${id}`, { method: 'DELETE' })
                    .then(() => loadPackages());
            }
        }

        document.getElementById('package-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name_en: document.getElementById('name_en').value,
                description_en: document.getElementById('description_en').value,
                cost: parseFloat(document.getElementById('cost').value),
                period: parseInt(document.getElementById('period').value),
                type: document.getElementById('type').value,
                ios_plan_id: document.getElementById('ios_plan_id').value,
                android_plan_id: document.getElementById('android_plan_id').value,
                status: document.getElementById('status').value === '1'
            };

            const url = editingId ? `/api/admin/packages/${editingId}` : '/api/admin/packages';
            const method = editingId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    loadPackages();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save package'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save package');
            });
        });

        // Load packages on page load
        loadPackages();
    </script>
</body>
</html>