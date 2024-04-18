<?php
include('config.php');

function getProductDetail($id) {
    global $conexionBD;
    $sql = "SELECT * FROM productos WHERE codprod = :id";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getOrderedProductsList($pagina, $productosPorPagina, $orden) {
    global $conexionBD;
    $offset = ($pagina - 1) * $productosPorPagina;
    $sql = "SELECT * FROM productos ORDER BY categoria $orden, pvp LIMIT :offset, :productosPorPag";
    $stmt = $conexionBD->prepare($sql);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':productosPorPag', $productosPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalRegistros = $conexionBD->query("SELECT COUNT(*) AS total FROM productos")->fetch(PDO::FETCH_ASSOC)['total'];
    return array(
        "productos" => $productos,
        "total_registros" => $totalRegistros
    );
}

function createProduct($data) {
    global $conexionBD;
    $sql = "INSERT INTO productos (nombre, categoria, pvp, stock, imagen, Observaciones) 
            VALUES (:nombre, :categoria, :pvp, :stock, :imagen, :observaciones)";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute([
        'nombre' => $data['nombre'],
        'categoria' => $data['categoria'],
        'pvp' => $data['pvp'],
        'stock' => $data['stock'],
        'imagen' => $data['imagen'],
        'observaciones' => $data['observaciones']
    ]);
    return $stmt->rowCount() > 0 ? array("message" => "Producto creado correctamente") : array("error" => "No se pudo crear el producto");
}

function updateProduct($id, $data) {
    global $conexionBD;
    $sql = "UPDATE productos 
            SET nombre = :nombre, categoria = :categoria, pvp = :pvp, stock = :stock, 
                imagen = :imagen, Observaciones = :observaciones 
            WHERE codprod = :id";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute([
        'id' => $id,
        'nombre' => $data['nombre'],
        'categoria' => $data['categoria'],
        'pvp' => $data['pvp'],
        'stock' => $data['stock'],
        'imagen' => $data['imagen'],
        'observaciones' => $data['observaciones']
    ]);
    return $stmt->rowCount() > 0 ? array("message" => "Producto actualizado correctamente") : array("message" => "No se encontró ningún producto con el ID proporcionado");
}

function removeProduct($id) {
    global $conexionBD;
    if (!isset($id) || !is_numeric($id)) {
        return array("error" => "ID de producto no válido");
    }
    $sql = "DELETE FROM productos WHERE codprod = :id";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->rowCount() > 0 ? array("message" => "Producto eliminado correctamente") : array("message" => "No se encontró ningún producto con el ID proporcionado");
}
?>
