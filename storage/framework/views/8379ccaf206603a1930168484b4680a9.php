<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MedicalSupplierz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
        .header { background: white; color: #1e40af; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .logo { display: flex; align-items: center; gap: 0.5rem; }
        .logo img { height: 40px; }
        .logo span { font-size: 1.5rem; font-weight: bold; }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a { color: #1e40af; text-decoration: none; font-weight: 500; }
        .nav-links a:hover { color: #2563eb; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        h1 { color: #1e40af; margin-bottom: 2rem; }
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem; }
        .contact-info { background: #f8fafc; padding: 2rem; border-radius: 8px; }
        .contact-form { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .form-group textarea { height: 120px; resize: vertical; }
        .submit-btn { background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; }
        .back-btn { background: #10b981; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 2rem; }
        .contact-item { margin-bottom: 1rem; }
        .contact-item strong { color: #1e40af; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <img src="/images/logo/Medical Supplierz.png" alt="MedicalSupplierz">
            </div>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/terms">Terms</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <a href="/" class="back-btn">← Back to Home</a>
        <h1>Contact Us</h1>
        <p>Get in touch with our team for any questions about medical conferences, workshops, or our platform.</p>
        
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Contact Information</h2>
                
                <div class="contact-item">
                    <strong>📧 Email:</strong><br>
                    General: info@medicalsupplierz.com<br>
                    Support: support@medicalsupplierz.com<br>
                    Legal: legal@medicalsupplierz.com
                </div>
                
                <div class="contact-item">
                    <strong>📞 Phone:</strong><br>
                    Main: +1 (555) 123-4567<br>
                    Support: +1 (555) 123-4568<br>
                    Toll-free: 1-800-MED-CONF
                </div>
                
                <div class="contact-item">
                    <strong>🏢 Address:</strong><br>
                    MedicalSupplierz Inc.<br>
                    123 Medical District<br>
                    Healthcare City, HC 12345<br>
                    United States
                </div>
                
                <div class="contact-item">
                    <strong>🕒 Business Hours:</strong><br>
                    Monday - Friday: 9:00 AM - 6:00 PM EST<br>
                    Saturday: 10:00 AM - 4:00 PM EST<br>
                    Sunday: Closed
                </div>
            </div>
            
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" required placeholder="Please describe your inquiry..."></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /var/www/html/events-ecommerce-admin/resources/views/contact.blade.php ENDPATH**/ ?>