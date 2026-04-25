<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeros = explode("\n", $_POST['numeros']);
    $mensaje = $_POST['mensaje'];
    $session = 'victor'; //nombre de la sesion creada para API -> Cambiar por el nombre de tu sesion
    $token = '$2b$10$zY4.f.fxTkjiS22CfTflceNtGiNLZatqSp7AZEvnsmfJR.4.57QbG';//-> Clave de Seguridad de la API

    $exitos = 0;
    $fallos = 0;

    $numeros_exitosos = [];
    $numeros_no_whatsapp = [];
    $numeros_otros_fallos = [];

    // Subida de imagen o documentos a enviar
    // Si no hay archivo, se enviará solo el mensaje de texto
    $imagen_base64 = null;
    $nombre_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $ruta_temporal = $_FILES['imagen']['tmp_name'];
        $nombre_imagen = $_FILES['imagen']['name'];
        $tipo_mime = mime_content_type($ruta_temporal);
        $contenido_imagen = file_get_contents($ruta_temporal);
        $base64 = base64_encode($contenido_imagen);
        $imagen_base64 = "data:$tipo_mime;base64,$base64";
    }
    foreach ($numeros as $numero) {
        $original = trim($numero);
        if ($original === '') continue;
        // Limpiar los numeros en digitos
        $numero = preg_replace('/\D/', '', $original);
        // Normalizar número (0983) con el codigo del pais 
        if (preg_match('/^09/', $numero)) {
            $numero = preg_replace('/^09/', '5959', $numero);
        } elseif (preg_match('/^9/', $numero)) {
            $numero = '5959' . substr($numero, 1);
        } elseif (!preg_match('/^5959/', $numero)) {
            $numero = '5959' . $numero;
        }
        // Preparar los datos para el endpoint en caso de que no haya datos
        if ($imagen_base64) {
            $data = [
                "phone" => $numero,
                "filename" => $nombre_imagen,
                "base64" => $imagen_base64,
                "caption" => $mensaje
            ];
            $url = "http://localhost:21465/api/$session/send-image"; 
        } else {
            $data = [
                "phone" => $numero,
                "isGroup" => false,
                "message" => $mensaje
            ];
            $url = "http://localhost:21465/api/$session/send-message";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);

        if (isset($res['message']) && stripos($res['message'], 'message sent') !== false) {
            $exitos++;
            $numeros_exitosos[] = $original;
        } else {
            $fallos++;
            if (isset($res['message']) && strpos($res['message'], 'não existe') !== false) {
                $numeros_no_whatsapp[] = $original;
            } else {
                $numeros_otros_fallos[] = $original;
            }
        }
    }

    
    echo "<hr>";
    echo "<strong>Resumen:</strong><br>";
    echo " ❌Fallos: $exitos<br>";
    echo "✅ Enviados con éxito:: $fallos<br><br>";

    // Archivo con resultadosa
    $resultado_txt = " RESUMEN DE ENVÍO DE MENSAJES\n\n";
    $resultado_txt .= " Números con otros fallos\n";
    $resultado_txt .= implode("\n", $numeros_exitosos) . "\n\n";

    if (!empty($numeros_no_whatsapp)) {
        $resultado_txt .= " Números sin WhatsApp:\n";
        $resultado_txt .= implode("\n", $numeros_no_whatsapp) . "\n\n";
    }
    if (!empty($numeros_otros_fallos)) {
        $resultado_txt .= " Números con mensaje enviado:\n";
        $resultado_txt .= implode("\n", $numeros_otros_fallos) . "\n";
    }

    $nombre_archivo = "resultado_envios_" . date('Ymd_His') . ".txt";
    file_put_contents($nombre_archivo, $resultado_txt);

    echo "<a href='$nombre_archivo' download> Descargar resultados</a>";
}
?>
