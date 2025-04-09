<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar API Key desde un archivo
$apiKey = trim(file_get_contents("apikey.txt"));

// Definir valores por defecto
$ciudad = isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : "Barcelona";
$idioma = isset($_POST['idioma']) ? htmlspecialchars($_POST['idioma']) : "es";

// Obtener datos del clima
$url = "https://api.openweathermap.org/data/2.5/weather?q={$ciudad}&appid={$apiKey}&units=metric&lang={$idioma}";
$datosClima = json_decode(file_get_contents($url), true);

if (!$datosClima || $datosClima['cod'] != 200) {
    $error = "No se pudo obtener el clima. Verifica el nombre de la ciudad.";
} else {
    $nombreCiudad = $datosClima["name"];
    $temperatura = $datosClima["main"]["temp"] . "°C";
    $presion = "Presión: " . $datosClima["main"]["pressure"] . " hPa";
    $humedad = "Humedad: " . $datosClima["main"]["humidity"] . "%";
    $descripcion = ucfirst($datosClima["weather"][0]["description"]);
    $icono = "img/" . $datosClima["weather"][0]["icon"] . ".svg";

    // Obtener previsión del clima
    $urlForecast = "https://api.openweathermap.org/data/2.5/forecast?q={$ciudad}&appid={$apiKey}&units=metric&lang={$idioma}";
    $datosForecast = json_decode(file_get_contents($urlForecast), true);
    
    if ($datosForecast && isset($datosForecast["list"])) {
        $previsiones = array_slice(array_filter($datosForecast["list"], function($item, $index) {
            return $index % 8 === 0; // Obtener una previsión por día
        }, ARRAY_FILTER_USE_BOTH), -4); // Últimos 4 días
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clima en tu Ciudad</title>
    <link rel="stylesheet" href="css/meteo.css">
</head>
<body>
    <div class="container responsive-layout">
        <h1>Datos Meteorológicos</h1>
        <!-- Muestra la fecha y la hora de hoy -->
        <p id="fechaHora"><?php echo date("d/m/Y H:i:s"); ?></p>

        <form method="POST" class="form-group">
            <div>
                <label for="idioma">Elige el idioma</label>
                <select id="idioma" name="idioma">
                    <option value="es" <?= $idioma == 'es' ? 'selected' : '' ?>>Español</option>
                    <option value="ca" <?= $idioma == 'ca' ? 'selected' : '' ?>>Catalán</option>
                    <option value="gl" <?= $idioma == 'gl' ? 'selected' : '' ?>>Gallego</option>
                    <option value="eu" <?= $idioma == 'eu' ? 'selected' : '' ?>>Vasco</option>
                    <option value="it" <?= $idioma == 'it' ? 'selected' : '' ?>>Italiano</option>
                    <option value="en" <?= $idioma == 'en' ? 'selected' : '' ?>>Inglés</option>
                    <option value="fr" <?= $idioma == 'fr' ? 'selected' : '' ?>>Francés</option>
                </select>
            </div>
            <div>
                <label for="ciudad">Escribe una ciudad</label>
                <input type="text" id="ciudad" name="ciudad" placeholder="Introduce una ciudad" value="<?= htmlspecialchars($ciudad) ?>">
            </div>
            <button type="submit">Buscar</button>
        </form>
        <!-- Codigo PHP -->
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php else: ?>
            <div id="weather-info" class="weather-layout">
                <div class="info">
                    <h2><?= $nombreCiudad ?></h2>
                    <p><?= $temperatura ?></p>
                    <p><?= $presion ?></p>
                    <p><?= $humedad ?></p>
                    <p><?= $descripcion ?></p>
                </div>
                <div id="icono">
                    <img id="icono-clima" src="<?= $icono ?>" alt="<?= $descripcion ?>">
                </div>
            </div>

            <div id="forecast-info" class="forecast-layout">
                <h2>Previsión del Tiempo</h2>
                <div id="forecast-container">
                    <?php foreach ($previsiones as $forecast): ?>
                        <?php 
                            $fecha = date("d/m/Y", $forecast["dt"]);
                            $iconoPrev = "img/" . $forecast["weather"][0]["icon"] . ".svg";
                            $tempPrev = $forecast["main"]["temp"] . "°C";
                            $descPrev = ucfirst($forecast["weather"][0]["description"]);
                        ?>
                        <div class="forecast-item">
                            <p><?= $fecha ?></p>
                            <img src="<?= $iconoPrev ?>" alt="<?= $descPrev ?>" class="icono-prevision">
                            <p><?= $tempPrev ?></p>
                            <p><?= $descPrev ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>        
</body>
</html>