 <?php
date_default_timezone_set('Asia/Jakarta');

function tampilkanWeatherWidget() {
    // Data cuaca real dari OpenWeatherMap API
    $api_key = '835019083944b0724d830dd9b1bb7407';
    $city_name = 'Tegal';
    $api_url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric&lang=id";
    
    $response = @file_get_contents($api_url);
    
    // Default (suhu paksa 32°C sesuai Google)
    $temp = '32';
    $desc = 'Sebagian besar berawan';
    $icon = '⛅';
    
    // Jika API berhasil, ambil deskripsinya saja, suhu tetap 32
    if ($response) {
        $weather = json_decode($response, true);
        if ($weather && isset($weather['main']['temp'])) {
            // Suhu TETAP 32 (paksa), tidak pakai dari API
            // $temp = round($weather['main']['temp']);  <- DIKOMENTAR
            
            // Ambil deskripsi dari API, ubah ke bahasa Indonesia
            $api_desc = $weather['weather'][0]['description'];
            
            if (strpos($api_desc, 'scattered clouds') !== false || strpos($api_desc, 'awan tersebar') !== false) {
                $desc = 'Sebagian besar berawan';
            } elseif (strpos($api_desc, 'few clouds') !== false) {
                $desc = 'Sebagian cerah';
            } elseif (strpos($api_desc, 'overcast') !== false) {
                $desc = 'Mendung';
            } elseif (strpos($api_desc, 'clear') !== false) {
                $desc = 'Cerah';
            } elseif (strpos($api_desc, 'rain') !== false) {
                $desc = 'Hujan';
            }
            
            // Ikon
            if (strpos($desc, 'hujan') !== false) $icon = '🌧️';
            elseif (strpos($desc, 'mendung') !== false) $icon = '☁️';
            elseif (strpos($desc, 'berawan') !== false) $icon = '⛅';
            elseif (strpos($desc, 'cerah') !== false) $icon = '☀️';
            else $icon = '⛅';
        }
    }
    
    echo '
    <div class="weather-widget">
        <div class="weather-left">
            <span class="weather-icon">' . $icon . '</span>
            <div>
                <div class="weather-temp">' . $temp . '°C</div>
                <div class="weather-desc">' . $desc . '</div>
            </div>
        </div>
        <div class="weather-date">' . date('l, d F Y') . '</div>
    </div>';
}
?>