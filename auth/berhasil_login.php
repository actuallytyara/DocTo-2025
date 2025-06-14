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
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="header">
        <div class="logo"> 
        <img src="../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;">DocTo</div>
        <div class="header-icons">
            <a href="profile_user.php" style="text-decoration: none; color: inherit;">
            <i class="fas fa-user"></i>
            </a>
            <i class="fas fa-envelope"></i>
            <a href="notifikasi.php" style="text-decoration: none; color: inherit;">
            <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>

    <div class="main-container">
        <div class="content-area">
            <div class="greeting-section">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="greeting-text">
                    <h2>Hai, <span id="username">nama</span>! Bagaimana harimu?</h2>
                    <div class="role-badge" id="userRole">role</div>
                </div>
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
                    <h4>Dokter</h4>
                    <p>Ngobrol enak bareng dokter</p>
                </div>
                <div class="feature-card">
                     <a href="../modules/janjitemu/index.php" style="text-decoration: none; color: inherit;">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Laporan</h4>
                    <p>Buat Janji temu</p>
                     </a>
                </div>
                <div class="feature-card"> 
                     <a href="../modules/pemesanan/keranjang.php" style="text-decoration: none; color: inherit;">
                    <div class="feature-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h4>Obat</h4>
                    <p> Kelola inventori obat</p>
                     </a>
                </div>
            </div>
        </div>

        
        <div class="sidebar">
            <div class="weather-widget">
                <div class="weather-icon loading">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="temperature loading">--°</div>
                <div class="weather-desc loading">Memuat cuaca...</div>
                
                <div class="weather-details">
                    <div class="weather-item">
                        <span><i class="fas fa-tint"></i> Kelembaban</span>
                        <span id="humidity">--%</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-wind"></i> Angin</span>
                        <span id="windSpeed">-- km/h</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-thermometer-half"></i> Tekanan</span>
                        <span id="pressure">-- hPa</span>
                    </div>
                    <div class="weather-item">
                        <span><i class="fas fa-eye"></i> Jarak Pandang</span>
                        <span id="visibility">-- km</span>
                    </div>
                </div>
            </div>

            <div class="calendar-widget">
                <div class="calendar-header" id="calendarHeader"></div>
                <div class="calendar-grid" id="calendarGrid"></div>
            </div>
            
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <script>
        const API_KEY = '43446587e0b3572d689f3882e091270f'; 
        const CITY = 'Surabaya';
        const COUNTRY_CODE = 'ID';

        async function getWeatherData() {
            try {
                const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${CITY},${COUNTRY_CODE}&appid=${API_KEY}&units=metric&lang=id`);
                const data = await response.json();
                
                if (response.ok) {
                    updateWeatherDisplay(data);
                } else {
                    console.error('Error fetching weather:', data.message);
                    showWeatherError();
                }
            } catch (error) {
                console.error('Network error:', error);
                showWeatherError();
            }
        }

        function updateWeatherDisplay(data) {
            const weatherIcon = document.querySelector('.weather-icon');
            const temperature = document.querySelector('.temperature');
            const weatherDesc = document.querySelector('.weather-desc');
            const humidity = document.getElementById('humidity');
            const windSpeed = document.getElementById('windSpeed');
            const pressure = document.getElementById('pressure');
            const visibility = document.getElementById('visibility');

            weatherIcon.classList.remove('loading');
            temperature.classList.remove('loading');
            weatherDesc.classList.remove('loading');

            const weatherCondition = data.weather[0].main.toLowerCase();
            let iconClass = 'fas ';
            
            switch(weatherCondition) {
                case 'clear':
                    iconClass += 'fa-sun';
                    break;
                case 'clouds':
                    iconClass += 'fa-cloud';
                    break;
                case 'rain':
                    iconClass += 'fa-cloud-rain';
                    break;
                case 'thunderstorm':
                    iconClass += 'fa-bolt';
                    break;
                case 'snow':
                    iconClass += 'fa-snowflake';
                    break;
                case 'mist':
                case 'haze':
                case 'fog':
                    iconClass += 'fa-smog';
                    break;
                default:
                    iconClass += 'fa-cloud-sun';
            }

            weatherIcon.innerHTML = `<i class="${iconClass}"></i>`;
            temperature.textContent = `${Math.round(data.main.temp)}°`;
            weatherDesc.textContent = data.weather[0].description;
            
            humidity.textContent = `${data.main.humidity}%`;
            windSpeed.textContent = `${Math.round(data.wind.speed * 3.6)} km/h`;
            pressure.textContent = `${data.main.pressure} hPa`;
            visibility.textContent = `${(data.visibility / 1000).toFixed(1)} km`;
        }

        function showWeatherError() {
            const weatherIcon = document.querySelector('.weather-icon');
            const temperature = document.querySelector('.temperature');
            const weatherDesc = document.querySelector('.weather-desc');

            weatherIcon.classList.remove('loading');
            temperature.classList.remove('loading');
            weatherDesc.classList.remove('loading');

            weatherIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            temperature.textContent = '--°';
            weatherDesc.textContent = 'Gagal memuat cuaca';
        }

        function createCalendar() {
            const now = new Date();
            const currentMonth = now.getMonth();
            const currentYear = now.getFullYear();
            const today = now.getDate();

            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

            document.getElementById('calendarHeader').textContent = `${monthNames[currentMonth]} ${currentYear}`;

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();

            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            dayNames.forEach(day => {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day header';
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            });

            for (let i = firstDay - 1; i >= 0; i--) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day other-month';
                dayElement.textContent = daysInPrevMonth - i;
                calendarGrid.appendChild(dayElement);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                if (day === today) {
                    dayElement.classList.add('today');
                }
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            }

            const totalCells = calendarGrid.children.length;
            const remainingCells = 49 - totalCells; 
            for (let day = 1; day <= remainingCells; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day other-month';
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            }
        }

        function loadProfileImage() {
            const userImage = "<?php echo isset($_SESSION['gambar']) && !empty($_SESSION['gambar']) ? '../' . htmlspecialchars($_SESSION['gambar']) : ''; ?>";
            
            if (userImage && userImage !== '../') {
                const profileImg = document.getElementById('profileImage');
                const defaultAvatar = document.getElementById('defaultAvatar');
                
                profileImg.src = userImage;
                profileImg.onload = function() {
                    profileImg.style.display = 'block';
                    defaultAvatar.style.display = 'none';
                };
                profileImg.onerror = function() {
                    profileImg.style.display = 'none';
                    defaultAvatar.style.display = 'flex';
                };
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const username = "<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'adi'; ?>";
            const role = "<?php echo isset($_SESSION['pengguna']) ? htmlspecialchars($_SESSION['pengguna']) : 'admin'; ?>";
            
            document.getElementById('username').textContent = username;
            document.getElementById('userRole').textContent = role;
            
            loadProfileImage();
            
            getWeatherData();
            
            createCalendar();
            
            setInterval(getWeatherData, 600000);
            
            const now = new Date();
            const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
            const msUntilMidnight = tomorrow.getTime() - now.getTime();
            
            setTimeout(() => {
                createCalendar();
                setInterval(createCalendar, 24 * 60 * 60 * 1000); 
            }, msUntilMidnight);
        });
    </script>
</body>
</html>