<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #1e40af; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
        .card-header { background: #f8fafc; padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background: #f8fafc; font-weight: bold; }
        .table tr:hover { background: #f9fafb; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.875rem; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
        .status-active { background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .status-inactive { background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin: 2rem 0; }
        .pagination button { padding: 0.5rem 1rem; border: 1px solid #d1d5db; background: white; cursor: pointer; }
        .pagination button.active { background: #1e40af; color: white; }
        .search-box { padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; width: 300px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Doctor Management</h1>
        <div>
            <a href="/admin" class="btn btn-secondary">Dashboard</a>
            <a href="/admin/subscriptions-management" class="btn btn-secondary">Subscriptions</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Registered Doctors</h2>
                <input type="text" id="searchBox" class="search-box" placeholder="Search doctors...">
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Hospital</th>
                        <th>Specialty</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="doctorsTable">
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">Loading doctors...</td>
                    </tr>
                </tbody>
            </table>
            
            <div id="pagination" class="pagination"></div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        
        async function loadDoctors(page = 1) {
            try {
                const response = await fetch(`/api/admin/doctors?page=${page}`);
                const result = await response.json();
                
                if (result.success) {
                    displayDoctors(result.data);
                    displayPagination(result.pagination);
                }
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }
        
        function displayDoctors(doctors) {
            const tbody = document.getElementById('doctorsTable');
            
            if (doctors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 2rem;">No doctors found.</td></tr>';
                return;
            }
            
            tbody.innerHTML = doctors.map(doctor => `
                <tr>
                    <td>${doctor.id}</td>
                    <td>${doctor.first_name} ${doctor.last_name}</td>
                    <td>${doctor.email}</td>
                    <td>${doctor.mobile_number || 'N/A'}</td>
                    <td>${doctor.company_name_en || 'N/A'}</td>
                    <td>ID: ${doctor.specialty_id || 'N/A'}</td>
                    <td>
                        <span class="status-${doctor.status ? 'active' : 'inactive'}">
                            ${doctor.status ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>${new Date(doctor.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-primary" onclick="viewDoctor(${doctor.id})">View</button>
                        <button class="btn ${doctor.status ? 'btn-danger' : 'btn-success'}" 
                                onclick="toggleStatus(${doctor.id}, ${doctor.status ? 0 : 1})">
                            ${doctor.status ? 'Deactivate' : 'Activate'}
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        function displayPagination(pagination) {
            const paginationDiv = document.getElementById('pagination');
            let buttons = '';
            
            for (let i = 1; i <= pagination.total_pages; i++) {
                buttons += `<button class="${i === pagination.current_page ? 'active' : ''}" 
                                   onclick="loadDoctors(${i})">${i}</button>`;
            }
            
            paginationDiv.innerHTML = buttons;
        }
        
        async function toggleStatus(doctorId, newStatus) {
            try {
                const response = await fetch(`/api/admin/doctors/${doctorId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                const result = await response.json();
                if (result.success) {
                    loadDoctors(currentPage);
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error updating status:', error);
            }
        }
        
        function viewDoctor(doctorId) {
            window.open(`/admin/doctors/${doctorId}`, '_blank');
        }
        
        // Load doctors on page load
        loadDoctors();
    </script>
</body>
</html><?php /**PATH /var/www/html/events-ecommerce-admin/resources/views/admin/doctors-management.blade.php ENDPATH**/ ?>