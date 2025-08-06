<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedicalSupplierz - Medical Conferences & Workshops</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
        
        .header { background: white; color: #1e40af; padding: 1rem 0; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .logo { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit; }
        .logo img { height: 40px; }
        .logo span { font-size: 1.5rem; font-weight: bold; }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a { color: #1e40af; text-decoration: none; transition: color 0.3s; font-weight: 500; }
        .nav-links a:hover { color: #2563eb; }
        
        .hero { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: white; padding: 8rem 2rem 4rem; text-align: center; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .hero p { font-size: 1.2rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .cta-btn { background: #10b981; color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s; }
        .cta-btn:hover { background: #059669; }
        
        .features { padding: 4rem 2rem; max-width: 1200px; margin: 0 auto; }
        .features h2 { text-align: center; margin-bottom: 3rem; font-size: 2.5rem; color: #1e40af; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .feature-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .feature-card h3 { color: #1e40af; margin-bottom: 1rem; }
        
        .registration { background: #f8fafc; padding: 4rem 2rem; }
        .reg-container { max-width: 800px; margin: 0 auto; text-align: center; }
        .reg-form { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 2rem; }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; }
        
        .footer { background: #1f2937; color: white; padding: 3rem 2rem 1rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; }
        .footer h3 { margin-bottom: 1rem; color: #60a5fa; }
        .footer a { color: #d1d5db; text-decoration: none; }
        .footer a:hover { color: white; }
        .footer-bottom { text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #374151; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="/" class="logo">
                <img src="/images/logo/Medical Supplierz.png" alt="MedicalSupplierz">
            </a>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="/doctor-register">Register</a></li>
                <li><a href="/terms">Terms</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero" id="home">
        <h1>Medical Conferences & Workshops</h1>
        <p>Join thousands of healthcare professionals in advancing medical knowledge through our comprehensive platform for medical conferences, workshops, and continuing education.</p>
        <a href="/doctor-register" class="cta-btn">Register as Doctor</a>
    </section>

    <section class="features" id="features">
        <h2>Why Choose MedicalSupplierz?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h3>🏥 Medical Conferences</h3>
                <p>Access to premium medical conferences worldwide with leading healthcare professionals and researchers.</p>
            </div>
            <div class="feature-card">
                <h3>🎓 Workshops & Training</h3>
                <p>Hands-on workshops and specialized training sessions to enhance your medical expertise and skills.</p>
            </div>
            <div class="feature-card">
                <h3>📱 Mobile App</h3>
                <p>Download our mobile app for easy access to event listings, registration, and networking opportunities.</p>
            </div>
            <div class="feature-card">
                <h3>🌍 Global Network</h3>
                <p>Connect with healthcare professionals from around the world and expand your professional network.</p>
            </div>
            <div class="feature-card">
                <h3>📜 CME Credits</h3>
                <p>Earn continuing medical education credits through our accredited programs and certifications.</p>
            </div>
            <div class="feature-card">
                <h3>💡 Latest Research</h3>
                <p>Stay updated with the latest medical research, innovations, and breakthrough discoveries.</p>
            </div>
        </div>
    </section>

    <section class="registration" id="download">
        <div class="reg-container">
            <h2>Download MedicalSupplierz App</h2>
            <p>Get instant access to medical conferences, workshops, and networking opportunities on your mobile device.</p>
            
            <div class="download-buttons" style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <a href="#" class="cta-btn" style="display: flex; align-items: center; gap: 0.5rem;">📱 Download for iOS</a>
                <a href="#" class="cta-btn" style="display: flex; align-items: center; gap: 0.5rem;">🤖 Download for Android</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 2rem; text-align: left;">
                <h3 style="color: #1e40af; margin-bottom: 1rem;">App Features:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">✅ Browse medical conferences worldwide</li>
                    <li style="margin-bottom: 0.5rem;">✅ Register for workshops and events</li>
                    <li style="margin-bottom: 0.5rem;">✅ Network with healthcare professionals</li>
                    <li style="margin-bottom: 0.5rem;">✅ Earn CME credits</li>
                    <li style="margin-bottom: 0.5rem;">✅ Access event materials and recordings</li>
                    <li style="margin-bottom: 0.5rem;">✅ Real-time notifications</li>
                </ul>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div>
                <h3>MedicalSupplierz</h3>
                <p>Your trusted platform for medical conferences, workshops, and continuing education opportunities.</p>
            </div>
            <div>
                <h3>Quick Links</h3>
                <p><a href="#features">Features</a></p>
                <p><a href="#download">Download</a></p>
                <p><a href="/terms">Terms & Conditions</a></p>
                <p><a href="/contact">Contact Us</a></p>
            </div>
            <div>
                <h3>Download App</h3>
                <p>Get our mobile app for easy access to medical events and networking opportunities.</p>
                <p><a href="#">📱 Download for iOS</a></p>
                <p><a href="#">📱 Download for Android</a></p>
            </div>
            <div>
                <h3>Contact Info</h3>
                <p>📧 info@medicalsupplierz.com</p>
                <p>📞 +965 94941155</p>
                <p>🏢 Block7, Street 72, Al-Ajeel Com Center, First Floor, Fahaheel, Ahmadi, Kuwait, 63007</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 MedicalSupplierz. All rights reserved.</p>
        </div>
    </footer>
</body>
</html><?php /**PATH /var/www/html/events-ecommerce-admin/resources/views/home.blade.php ENDPATH**/ ?>