<?php
/*
 * index.php
 * 
 * Copyright 2019 Juan Manuel Dedionigis <jmdedio@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cobranzas</title>
    <link href="http://getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body>
    <div id="container">
	<h2>Cobranzas</h2>
	<p>Ingrese un archivo</p>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
            <div>
                <input type="file" name="uploadedFile"/>
            </div>
            <input type="submit" name="Cargar" value="Cargar"/>
        </form>
    </div>
<?php
require_once "debitos.php";
session_start();
$message = '';

// Verifica que se haya accionado el botón Cargar
if(!empty($_POST['Cargar'] && $_POST['Cargar'] == 'Cargar')){
    // Verifica que se haya añadido un archivo
    if(isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK){
        // Detalles del archivo
        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];
        $fileSize = $_FILES['uploadedFile']['size'];
        $fileType = $_FILES['uploadedFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower($fileNameCmps[1]);

        // sanitiza el nombre del archivo
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Verifica la extensión del archivo
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'txt', 'xls', 'doc');
        if(in_array($fileExtension, $allowedfileExtensions)){
            // Ruta para alojar el archivo
            $uploadFileDir = './ficheros_subidos/';
            $dest_path = $uploadFileDir . $newFileName;

            // Si se alojó el archivo ejecuta el análisis
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                #$message ='File is successfully uploaded.';
                $deb = new CobranzasClass();
                $deb->extrae_registros($dest_path);
                $deb->rend_cobranzas();

            } else{
                $message = 'Hubo algún error al subir el fichero al directorio. Por favor asegúrese que el directorio tiene permisos de escritura.';
            }
        } else{
            $message = 'Fallo de carga.  Tipo de extensiones permitidas: ' . implode(',', $allowedfileExtensions);
        }
    } else{
        $message = 'Hubo algún error al cargar el archivo.<br>';
        $message .= 'Error:' . $_FILES['uploadedFile']['error'];
    }
}
// Verifica la sesión
if(!empty($_SESSION['message']) && $_SESSION['message'])
{
  printf('<b>%s</b>', $_SESSION['message']);
  unset($_SESSION['message']);
}

?>
    <table>
        <?php foreach($deb->debitos as $reg): ?>
        <tr>
            <td><?php echo $reg['cbu']; ?></td>
            <td><?php echo date('Y-m-d', $reg['fecha']); ?></td>
            <td><?php echo $reg['estado']; ?></td>
            <td><?php echo $reg['importe']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
