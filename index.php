<?php
include('config.php');

$conexionBD =  conectar($bd);

// Obtener la ruta de la solicitud
$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Definir el enrutamiento
$endpoints = array(
    'GET' => array(
        '/productDetail' => 'getProductDetail',  
        '/orderedProductsList' => 'getOrderedProductsList'
    ),
    'POST' => array(
        '/addProduct' => 'createProduct'
    ),
    'PUT' => array(
        '/updateProduct' => 'updateProduct'
    ),
    'DELETE' => array(
        '/removeProduct' => 'removeProduct'
    )
);

// Buscar la ruta correspondiente
$foundPath = null;
foreach ($endpoints[$method] as $endpoint => $funcion) {
    if (strpos($path, $endpoint) !== false) {
        $foundPath = $funcion;
        break;
    }
}

// Si no se encuentra la ruta, devolver un error con código 400
if ($foundPath === null) {
    http_response_code(400);
    $response = array("error" => "Ruta no encontrada");
} else {
    $response = call_user_func($foundPath, $_GET);
    if (isset($response['error'])) {
        // Código de estado HTTP a 400 si hay un error
        http_response_code(400); 
    } else {
        // Código de estado HTTP a 200 si todo está bien
        http_response_code(200);
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);


function getProductDetail($params) {
    global $conexionBD;
    $id = isset($params['id']) ? $params['id'] : null;
    if ($id !== null) {
        $sql = "SELECT * FROM productos WHERE codprod = :id";
        $stmt = $conexionBD->prepare($sql);
        $stmt->execute(['id' => $id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            return $producto;
        } else {
            return array("message" => "No se encontró ningún producto con el ID proporcionado");
        }
    } else {
        return array("error" => "ID de producto no proporcionado");
    }
}

function getOrderedProductsList($params) {
    global $conexionBD;
    $pagina = isset($params['pagina']) ? intval($params['pagina']) : 1;
    $productosPorPagina = isset($params['productosPorPag']) ? intval($params['productosPorPag']) : 5;
    $offset = ($pagina - 1) * $productosPorPagina;

    $orden = isset($params['orden']) ? strtoupper($params['orden']) : 'ASC';

    $sql = "SELECT * FROM productos ORDER BY categoria $orden, pvp LIMIT :offset, :productosPorPag";
    $stmt = $conexionBD->prepare($sql);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':productosPorPag', $productosPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlTotal = "SELECT COUNT(*) AS total FROM productos";
    $stmtTotal = $conexionBD->query($sqlTotal);
    $totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    return array(
        "productos" => $productos,
        "total_registros" => $totalRegistros
    );
}


function createProduct() {
    global $conexionBD;
    $data = json_decode(file_get_contents('php://input'), true);
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

    if ($stmt->rowCount() > 0) {
        return array("message" => "Producto creado correctamente");
    } else {
        return array("error" => "No se pudo crear el producto");
    }
}

function updateProduct($params) {
    global $conexionBD;
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($params['id']) ? $params['id'] : null;
    if ($id !== null) {
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

        if ($stmt->rowCount() > 0) {
            return array("message" => "Producto actualizado correctamente");
        } else {
            return array("message" => "No se encontró ningún producto con el ID proporcionado");
        }
    } else {
        return array("error" => "ID de producto no proporcionado");
    }
}

function removeProduct($params) {
    global $conexionBD;
    $id = isset($params['id']) ? $params['id'] : null;
    if (!isset($id) || !is_numeric($id)) {
        return array("error" => "ID de producto no válido");
    }
    $sql = "DELETE FROM productos WHERE codprod = :id";
    $stmt = $conexionBD->prepare($sql);
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() > 0) {
        return array("message" => "Producto eliminado correctamente");
    } else {
        return array("message" => "No se encontró ningún producto con el ID proporcionado");
    }
}

?>