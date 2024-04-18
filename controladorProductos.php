<?php
include('modeloProductos.php');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

switch ($method) {
    case 'GET':
        if (strpos($path, '/productDetail') !== false) {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $response = getProductDetail($id);
        } elseif (strpos($path, '/orderedProductsList') !== false) {
            $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
            $productosPorPagina = isset($_GET['productosPorPag']) ? intval($_GET['productosPorPag']) : 5;
            $orden = isset($_GET['orden']) ? strtoupper($_GET['orden']) : 'ASC';
            $response = getOrderedProductsList($pagina, $productosPorPagina, $orden);
        }
        break;
    case 'POST':
        if (strpos($path, '/addProduct') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = createProduct($data);
        }
        break;
    case 'PUT':
        if (strpos($path, '/updateProduct') !== false) {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $data = json_decode(file_get_contents('php://input'), true);
            $response = updateProduct($id, $data);
        }
        break;
    case 'DELETE':
        if (strpos($path, '/removeProduct') !== false) {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $response = removeProduct($id);
        }
        break;
    default:
        $response = array("error" => "Método HTTP no válido");
}

http_response_code(isset($response['error']) ? 400 : 200);
header('Content-Type: application/json');
echo json_encode($response);
?>
