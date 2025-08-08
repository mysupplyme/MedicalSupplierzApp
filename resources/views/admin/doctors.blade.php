<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Doctors - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #1e40af; color: white; padding: 1rem 2rem; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #f8fafc; padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background: #f8fafc; font-weight: bold; }
        .table tr:hover { background: #f9fafb; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .back-btn { background: #6b7280; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard - Registered Doctors</h1>
    </div>

    <div class="container">
        <a href="/admin" class="back-btn">← Back to Dashboard</a>
        
        <div class="card">
            <div class="card-header">
                <h2>Registered Doctors</h2>
                <p>Total: <span id="total-count">Loading...</span></p>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Hospital/Clinic</th>
                        <th>Specialty</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody id="doctors-table">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">Loading doctors...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        async function loadDoctors() {
            try {
                const response = await fetch('/api/common/doctors');
                const result = await response.json();
                
                if (result.success && result.data) {
                    const tbody = document.getElementById('doctors-table');
                    const totalCount = document.getElementById('total-count');
                    
                    totalCount.textContent = result.data.length;
                    
                    if (result.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No doctors registered yet.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = result.data.map(doctor => `
                        <tr>
                            <td>${doctor.id}</td>
                            <td>${doctor.first_name} ${doctor.last_name}</td>
                            <td>${doctor.email}</td>
                            <td>${doctor.mobile_number || 'N/A'}</td>
                            <td>${doctor.company_name_en || 'N/A'}</td>
                            <td>ID: ${doctor.specialty_id || 'N/A'}</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>${new Date(doctor.created_at).toLocaleDateString()}</td>
                        </tr>
                    `).join('');
                } else {
                    document.getElementById('doctors-table').innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Error loading doctors.</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('doctors-table').innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Error loading doctors.</td></tr>';
            }
        }

        // Load doctors on page load
        loadDoctors();
    </script>
</body>
</html>