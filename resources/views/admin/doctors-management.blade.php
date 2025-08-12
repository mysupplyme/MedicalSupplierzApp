<!DOCTYPE html>
<html>
<head>
    <title>Doctors Management - {{ config('app.name') }}</title>
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
        .btn-danger { background: #dc3545; color: white; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Doctors Management</h1>
        <p>Manage registered doctors and their subscriptions</p>
    </div>
    
    <div class="content">
        <div id="doctors-list">
            <h2>Registered Doctors</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Specialty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="doctors-table">
                    <tr>
                        <td colspan="6">Loading doctors...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Load doctors data
        fetch('/api/admin/doctors')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('doctors-table');
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(doctor => `
                        <tr>
                            <td>${doctor.id}</td>
                            <td>${doctor.name}</td>
                            <td>${doctor.email}</td>
                            <td>${doctor.speciality || 'N/A'}</td>
                            <td class="status-${doctor.status || 'active'}">${doctor.status || 'Active'}</td>
                            <td>
                                <button class="btn btn-primary" onclick="viewDoctor(${doctor.id})">View</button>
                                <button class="btn btn-danger" onclick="deleteDoctor(${doctor.id})">Delete</button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No doctors found</td></tr>';
                }
            })
            .catch(error => {
                document.getElementById('doctors-table').innerHTML = '<tr><td colspan="6">Error loading doctors</td></tr>';
            });

        function viewDoctor(id) {
            window.location.href = `/admin/doctors/${id}`;
        }

        function deleteDoctor(id) {
            if (confirm('Are you sure you want to delete this doctor?')) {
                fetch(`/api/admin/doctors/${id}`, { method: 'DELETE' })
                    .then(() => location.reload());
            }
        }
    </script>
</body>
</html>