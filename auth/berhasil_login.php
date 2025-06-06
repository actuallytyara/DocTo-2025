<?php
session_start();
include('../database/koneksi.php');
 
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(90deg, #356859, #37966f);
            color: white;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);

        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
        }

        .logo::before {
            font-family: "Font Awesome 6 Free";
            font-weight: bold;
            margin-right: 10px;
            font-size: 1rem;
            color: #fff;
        }

        .header-icons {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .header-icons i {
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .header-icons i:hover {
            transform: scale(1.1);
        }

        .main-container {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .content-area {
            flex: 1;
            padding: 30px;
            background: white;
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .greeting-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(90deg, #356859, #37966f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
        }

        .greeting-text h2 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }

        .role-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .search-bar {
            margin-bottom: 30px;
        }

        .search-bar input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-bar input:focus {
            border-color: #4a9d4e;
        }

        .motivation-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .motivation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            z-index: -1;
        }

        .motivation-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            margin-right: 20px;
            background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80');
            background-size: cover;
            background-position: center;
        }

        .motivation-text {
            flex: 1;
        }

        .motivation-text h3 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #333;
        }

        .motivation-text p {
            color: #666;
            font-size: 14px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid #f0f0f0;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #4a9d4e;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: #356859;
        }

        .feature-card h4 {
            color: #333;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            font-size: 12px;
        }

        .sidebar {
            width: 300px;
            background: linear-gradient(180deg, #87CEEB, #4682B4);
            padding: 30px 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            z-index: 0;
        }

        .weather-widget {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .weather-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .temperature {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .weather-desc {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .weather-details {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
        }

        .weather-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .logout-btn {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            font-weight: 500;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: -1;
            }
            
            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        ::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
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
    <div class="header">
        <div class="logo"> <img src="../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;">DocTo</div>
        <div class="header-icons">
            <i class="fas fa-user"></i>
            <i class="fas fa-envelope"></i>
            <i class="fas fa-bell"></i>
        </div>
    </div>

    <div class="main-container">
        <div class="content-area">
            <div class="greeting-section">
                <div class="avatar">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="greeting-text">
                    <h2>Hai, <span id="username">nama</span>! Bagaimana hari ini?</h2>
                    <div class="role-badge" id="userRole">role</div>
                </div>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="🔍 Cari sesuatu">
            </div>

            <div class="motivation-card">
                <div class="motivation-image"></div>
                <div class="motivation-text">
                    <h3>Motivasi Harian</h3>
                    <p>Kesehatan adalah kekayaan yang sesungguhnya. Jaga tubuh Anda hari ini!</p>
                </div>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Pasien</h4>
                    <p>Kelola data pasien</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Laporan</h4>
                    <p>Lihat statistik harian</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h4>Obat</h4>
                    <p>Kelola inventori obat</p>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="weather-widget">
                <div class="weather-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="temperature">28°</div>
                <div class="weather-desc">Cerah Berawan</div>
                
                <div class="weather-details">
                    <div class="weather-item">
                        <span><i class="fas fa-tint"></i> Kelembaban</span>
                        <span>65%</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-wind"></i> Angin</span>
                        <span>12 km/h</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-thermometer-half"></i> Tekanan</span>
                        <span>1013 hPa</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-sun"></i> UV Index</span>
                        <span>Sedang</span>
                    </div>
                </div>
            </div>
            
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <script> 
        document.addEventListener('DOMContentLoaded', function() { 
            const username = "<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'adi'; ?>";
            const role = "<?php echo isset($_SESSION['pengguna']) ? htmlspecialchars($_SESSION['pengguna']) : 'admin'; ?>";
            
            document.getElementById('username').textContent = username;
            document.getElementById('userRole').textContent = role;
             
            const weatherIcon = document.querySelector('.weather-icon');
            setInterval(() => {
                weatherIcon.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    weatherIcon.style.transform = 'scale(1)';
                }, 200);
            }, 3000);
        });
    </script>
</body>
</html>