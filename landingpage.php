<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTo - Platform Kesehatan Terdepan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(54, 150, 130, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }

          .logo-img {
            height: 40px;
            width: auto;
            margin-right: 0.5rem;
        }

        .logo::before {
            display: none; /* Hide the emoji since we're using actual logo */
        } 
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #a8e6cf;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #a8e6cf;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* HEROOOOOOOOOOO */
        .hero {
            height: 100vh;
            background: linear-gradient(135deg, #369682 0%, #2c7a6b 50%, #1e5c54 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><path d="M0,400 Q300,200 600,400 T1200,400 V800 H0 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
            background-position: bottom;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: normal;
            margin-bottom: 1rem;
            letter-spacing: 2px;
            opacity: 0;
            animation: fadeInUp 1s ease 0.5s forwards;
        }

        .hero .subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            font-weight: 300;
            opacity: 0;
            animation: fadeInUp 1s ease 0.8s forwards;
        }

        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.8;
            line-height: 1.8;
            opacity: 0;
            animation: fadeInUp 1s ease 1.1s forwards;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 1s ease 1.4s forwards;
        }

        .cta-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Medical Image Section */
        .medical-hero {
            position: absolute;
            right: 10%;
            top: 50%;
            transform: translateY(-50%);
            width: 300px;
            height: 400px;
            background: url('https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            opacity: 0;
            animation: slideInRight 1s ease 1.2s forwards;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #369682;
            margin-bottom: 3rem;
            font-weight: 300;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #369682, #2c7a6b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .feature-card h3 {
            color: #369682;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Services Section */
        .services {
            padding: 6rem 0;
            background: white;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .service-item {
            position: relative;
            height: 300px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .service-item:hover {
            transform: scale(1.05);
        }

        .service-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(54, 150, 130, 0.8), rgba(44, 122, 107, 0.8));
            z-index: 1;
        }

        .service-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem;
            color: white;
            z-index: 2;
        }

        .service-content h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .service-content p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #369682, #2c7a6b);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 300;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1e5c54;
            color: white;
            padding: 3rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #a8e6cf;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .footer-section ul li a:hover {
            opacity: 1;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #2c7a6b;
            opacity: 0.7;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px) translateY(-50%);
            }
            to {
                opacity: 1;
                transform: translateX(0) translateY(-50%);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .medical-hero {
                display: none;
            }

            .hero-content {
                padding: 0 1rem;
            }

            .features-grid,
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }


        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #356859 0%, #2a5346 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #356859 0%, #2a5346 100%);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <img src="assets/images/logo_docto.png" style="height: 55px; margin-right: 10px; margin-top: -5px;">DocTo</a>
            <ul class="nav-links">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#artikel">Artikel</a></li>
                <li><a href="#katalog">Katalog Obat</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#register">Register</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Temukan solusi kesehatan</h1>
            <p class="subtitle">terbaik untuk hidup yang lebih sehat</p>
            <p class="hero-description">
                Platform kesehatan terpercaya yang menyediakan informasi medis terkini, 
                katalog obat lengkap, dan tips kesehatan untuk mendukung gaya hidup sehat Anda.
            </p>
            <a href="index.php" class="cta-button">Mulai Sekarang</a>
        </div>
        <div class="medical-hero"></div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Layanan Unggulan</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Artikel Kesehatan</h3>
                    <p>Akses ribuan artikel kesehatan terpercaya yang ditulis oleh para ahli medis berpengalaman.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💊</div>
                    <h3>Katalog Obat</h3>
                    <p>Database obat lengkap dengan informasi dosis, efek samping, dan panduan penggunaan yang tepat.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏃‍♂️</div>
                    <h3>Tips Hidup Sehat</h3>
                    <p>Panduan praktis untuk menjaga kesehatan dengan pola makan, olahraga, dan gaya hidup yang baik.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="services">
        <div class="container">
            <h2 class="section-title">Jelajahi Fitur Kami</h2>
            <div class="services-grid">
                <div class="service-item" style="background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;">
                    <div class="service-content">
                        <h3>Pola Makan Sehat</h3>
                        <p>Panduan nutrisi lengkap untuk mendukung kesehatan optimal setiap hari.</p>
                    </div>
                </div>
                <div class="service-item" style="background: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;">
                    <div class="service-content">
                        <h3>Olahraga di Rumah</h3>
                        <p>Program latihan efektif yang bisa dilakukan di rumah tanpa peralatan mahal.</p>
                    </div>
                </div>
                <div class="service-item" style="background: url('https://images.unsplash.com/photo-1559757175-0eb30cd8c063?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;">
                    <div class="service-content">
                        <h3>Manajemen Stres</h3>
                        <p>Teknik relaksasi dan mindfulness untuk mengelola stres dalam kehidupan sehari-hari.</p>
                    </div>
                </div>
                <div class="service-item" style="background: url('https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;">
                    <div class="service-content">
                        <h3>Konsultasi Online</h3>
                        <p>Layanan konsultasi kesehatan dengan tenaga medis profesional secara online.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2>Mulai Perjalanan Kesehatan Anda</h2>
            <p>Bergabunglah dengan ribuan pengguna yang telah merasakan manfaat platform DocTo</p>
            <a href="auth/Register.php" class="cta-button">Daftar Sekarang</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>DocTo</h3>
                    <p>Platform kesehatan terpercaya untuk mendukung gaya hidup sehat Anda dengan informasi medis terkini dan layanan berkualitas.</p>
                </div>
                <div class="footer-section">
                    <h3>Layanan</h3>
                    <ul>
                        <li><a href="#">Artikel Kesehatan</a></li>
                        <li><a href="#">Katalog Obat</a></li>
                        <li><a href="#">Tips Hidup Sehat</a></li>
                        <li><a href="#">Konsultasi Online</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Tentang Kami</h3>
                    <ul>
                        <li><a href="#">Tim Kami</a></li>
                        <li><a href="#">Visi & Misi</a></li>
                        <li><a href="#">Karir</a></li>
                        <li><a href="#">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Dukungan</h3>
                    <ul>
                        <li><a href="#">Pusat Bantuan</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 DocTo. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(54, 150, 130, 0.98)';
            } else {
                navbar.style.background = 'rgba(54, 150, 130, 0.95)';
            }
        });

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card').forEach(card => { 
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>