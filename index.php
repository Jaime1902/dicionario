<!-- Enlaza los archivos CSS y JavaScript de Bootstrap a través de CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<style>
    body {
        padding-top: 60px; /* Para asegurar que la sección de búsqueda no se superponga con el contenido */
    }

    .card {
        margin-bottom: 20px;
    }

    .historial-btn {
        margin-top: 10px;
    }
</style>

<!-- Formulario de entrada -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">Diccionario</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <h2>Buscar Palabra</h2>
            <form method="POST" action="">
                <div class="input-group mb-3">
                    <input type="text" id="palabra" name="palabra" class="form-control" placeholder="Palabra" required>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
        <div class="col-md-8">
            <?php
            require 'vendor/autoload.php';
            use Goutte\Client;
            
            if (isset($_POST['palabra'])) {
                $palabra = $_POST['palabra'];
                $url = 'https://dle.rae.es/' . urlencode($palabra);

                $client = new Client();
                $crawler = $client->request('GET', $url);

                // Utiliza selectores CSS para extraer los datos deseados
                $definicion = $crawler->filter('article')->html();

                // Genera el archivo de texto con la información
                $archivo = fopen('resultado.txt', 'w');
                fwrite($archivo, $definicion);
                fclose($archivo);

                // Genera el archivo JSON con la información
                $data = array('palabra' => $palabra, 'definicion' => $definicion);
                $json = json_encode($data);
                file_put_contents('resultado.json', $json, FILE_APPEND);

                // Agrega el registro al historial
                if (file_exists('historial.json')) {
                    $historial = json_decode(file_get_contents('historial.json'), true);
                } else {
                    $historial = array();
                }
                $historial[] = $data;
                file_put_contents('historial.json', json_encode($historial));

                // Muestra el contenido en la página con estilos de Bootstrap
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo $definicion;
                echo '</div>';
                echo '</div>';
            }

            // Mostrar historial de búsquedas
            if (file_exists('historial.json')) {
                $historial = json_decode(file_get_contents('historial.json'), true);

                // Verificar si el historial no está vacío
                if (!empty($historial)) {
                    echo '<button class="btn btn-secondary historial-btn" data-bs-toggle="collapse" data-bs-target="#historial" aria-expanded="false" aria-controls="historial">Historial</button>';
                    echo '<div class="collapse mt-3" id="historial">';
                    echo '<h3>Historial de búsquedas:</h3>';
                    echo '<ul>';
                    foreach ($historial as $item) {
                        echo '<li>';
                        echo '<strong>Palabra:</strong> ' . $item['palabra'] . '<br>';
                        echo '<strong>Definición:</strong> ' . $item['definicion'];
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>
