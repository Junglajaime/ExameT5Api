<?php
$bd = [
    'host' => 'localhost',
    'nombreUsuario' => 'root',
    'contraseña' => '',
    'bd' => 'bdproductos'
];

// Conectar a la Base de Datos
function conectar($bd)
{
    try {
        $conexionBD = new PDO("mysql:host={$bd['host']};dbname={$bd['bd']}", $bd['nombreUsuario'], $bd['contraseña']);
        $conexionBD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexionBD;
    } catch (PDOException $exception) {
        exit($exception->getMessage());
    }
}
?>
