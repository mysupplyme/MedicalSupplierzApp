<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration - MedicalSupplierz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; background: #f8fafc; }
        .header { background: white; color: #1e40af; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .logo { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit; }
        .logo img { height: 40px; }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a { color: #1e40af; text-decoration: none; font-weight: 500; }
        .container { max-width: 600px; margin: 2rem auto; padding: 0 2rem; }
        .form-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; margin-bottom: 1rem; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #374151; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 1rem; }
        .form-row { display: flex; gap: 1rem; }
        .form-row .form-group { flex: 1; margin-bottom: 1rem; }
        .form-row .form-group.small { flex: 0 0 25%; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1e40af; }
        .submit-btn { width: 100%; background: #10b981; color: white; padding: 0.75rem; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        .submit-btn:hover { background: #059669; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #1e40af; text-decoration: none; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; display: none; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; display: none; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="/" class="logo">
                <img src="/images/logo/Medical Supplierz.png" alt="MedicalSupplierz">
            </a>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/login">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <a href="/" class="back-link">← Back to Home</a>
        
        <div class="form-card">
            <h1>Doctor Registration</h1>
            <div id="success-msg" class="success-msg"></div>
            <div id="error-msg" class="error-msg"></div>
            
            <form id="doctor-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <div class="form-row">
                    <div class="form-group small">
                        <label for="country_code">Country Code *</label>
                        <select id="country_code" name="country_code" required>
                            <option value="">Select Country Code</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number *</label>
                        <input type="tel" id="mobile_number" name="mobile_number" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="current_position">Current Position</label>
                    <input type="text" id="current_position" name="current_position">
                </div>
                
                <div class="form-group">
                    <label for="workplace">Workplace/Department</label>
                    <input type="text" id="workplace" name="workplace">
                </div>
                
                <div class="form-group">
                    <label for="specialty_id">Medical Specialty *</label>
                    <select id="specialty_id" name="specialty_id" required>
                        <option value="">Select Specialty</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sub_specialty_id">Sub-Specialty</label>
                    <select id="sub_specialty_id" name="sub_specialty_id">
                        <option value="">Select Sub-Specialty</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nationality">Nationality</label>
                    <select id="nationality" name="nationality">
                        <option value="">Select Nationality</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="residency">Country of Residence</label>
                    <select id="residency" name="residency">
                        <option value="">Select Country</option>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn">Register as Doctor</button>
            </form>
        </div>
    </div>

    <script>
        // Load dropdown data
        async function loadDropdowns() {
            try {
                // Load specialties
                const specialtiesRes = await fetch('/api/common/get_specialties');
                const specialties = await specialtiesRes.json();
                const specialtySelect = document.getElementById('specialty_id');
                if (specialties.data) {
                    specialties.data.forEach(specialty => {
                        specialtySelect.innerHTML += `<option value="${specialty.id}">${specialty.title_en || specialty.name}</option>`;
                    });
                }

                // Load countries for nationality
                const nationalitiesRes = await fetch('/api/common/get_nationalities');
                const nationalities = await nationalitiesRes.json();
                const nationalitySelect = document.getElementById('nationality');
                if (nationalities.data) {
                    nationalities.data.sort((a, b) => a.title.localeCompare(b.title));
                    nationalities.data.forEach(country => {
                        nationalitySelect.innerHTML += `<option value="${country.id}">${country.title}</option>`;
                    });
                }

                // Load countries for residency
                const residenciesRes = await fetch('/api/common/get_residencies');
                const residencies = await residenciesRes.json();
                const residencySelect = document.getElementById('residency');
                if (residencies.data) {
                    residencies.data.sort((a, b) => (a.title_en || a.name).localeCompare(b.title_en || b.name));
                    residencies.data.forEach(country => {
                        residencySelect.innerHTML += `<option value="${country.id}">${country.title_en || country.name}</option>`;
                    });
                }

                // Load country codes
                const countryCodesRes = await fetch('/api/common/get_country_codes');
                const countryCodes = await countryCodesRes.json();
                const countryCodeSelect = document.getElementById('country_code');
                if (countryCodes.data) {
                    countryCodes.data.sort((a, b) => a.name.localeCompare(b.name));
                    countryCodes.data.forEach((country, index) => {
                        const isKuwait = country.name && country.name.toLowerCase().includes('kuwait');
                        const option = `<option value="${country.id}" ${isKuwait ? 'selected' : ''}>${country.name} (${country.phone_code || country.phone_prefix})</option>`;
                        countryCodeSelect.innerHTML += option;
                    });
                }
            } catch (error) {
                console.error('Error loading dropdowns:', error);
            }
        }

        // Load sub-specialties when specialty changes
        document.getElementById('specialty_id').addEventListener('change', async (e) => {
            const specialtyId = e.target.value;
            const subSpecialtySelect = document.getElementById('sub_specialty_id');
            subSpecialtySelect.innerHTML = '<option value="">Select Sub-Specialty</option>';
            
            if (specialtyId) {
                try {
                    const response = await fetch(`/api/common/get_sub_specialties?specialty_id=${specialtyId}`);
                    const result = await response.json();
                    if (result.data) {
                        result.data.forEach(subSpecialty => {
                            subSpecialtySelect.innerHTML += `<option value="${subSpecialty.id}">${subSpecialty.title_en || subSpecialty.name}</option>`;
                        });
                    }
                } catch (error) {
                    console.error('Error loading sub-specialties:', error);
                }
            }
        });

        // Handle form submission
        document.getElementById('doctor-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('success-msg').textContent = result.message;
                    document.getElementById('success-msg').style.display = 'block';
                    document.getElementById('error-msg').style.display = 'none';
                    e.target.reset();
                } else {
                    document.getElementById('error-msg').textContent = result.message || 'Registration failed';
                    document.getElementById('error-msg').style.display = 'block';
                    document.getElementById('success-msg').style.display = 'none';
                }
            } catch (error) {
                document.getElementById('error-msg').textContent = 'Network error. Please try again.';
                document.getElementById('error-msg').style.display = 'block';
                document.getElementById('success-msg').style.display = 'none';
            }
        });

        // Load dropdowns on page load
        loadDropdowns();
    </script>
</body>
</html>