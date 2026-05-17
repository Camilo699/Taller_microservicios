<?php

namespace App\Presentation\Repositories;

use App\Controllers\RetroItemController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RetroItemRepository
{
    function list(Request $request, Response $response)
    {
        try {
            $controller = new RetroItemController();
            $items = $controller->getItems();
            $response->getBody()->write($items->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode(['msg' => 'Error al obtener los items']));
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function listPorSprint(Request $request, Response $response, $args)
    {
        try {
            $sprint_id = $args['sprint_id'];
            $controller = new RetroItemController();
            $items = $controller->getItemsPorSprint($sprint_id);
            $response->getBody()->write($items->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "No hay items para el sprint {$args['sprint_id']}"]));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function listPorCategoria(Request $request, Response $response, $args)
    {
        try {
            $sprint_id = $args['sprint_id'];
            $categoria = $args['categoria'];
            $controller = new RetroItemController();
            $items = $controller->getItemsPorCategoria($sprint_id, $categoria);
            $response->getBody()->write($items->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 3) {
                $code = 406;
                $response->getBody()->write(json_encode(['msg' => "Categoría inválida: {$args['categoria']}"]));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function create(Request $request, Response $response)
    {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            $controller = new RetroItemController();
            $item = $controller->guardarItem($data);
            $response->getBody()->write($item->toJson());
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 1) {
                $code = 406;
                $response->getBody()->write(json_encode(['msg' => 'Datos incorrectos: sprint_id, categoria y descripcion son obligatorios']));
            } elseif ($ex->getCode() == 3) {
                $code = 406;
                $response->getBody()->write(json_encode(['msg' => 'Categoría inválida. Use: accion, logro, impedimento, comentario u otro']));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function detail(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $controller = new RetroItemController();
            $item = $controller->getItem($id);
            $response->getBody()->write($item->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Item {$args['id']} no encontrado"]));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function update(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            $controller = new RetroItemController();
            $item = $controller->modificarItem($id, $data);
            $response->getBody()->write($item->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Item {$args['id']} no encontrado"]));
            } elseif ($ex->getCode() == 3) {
                $code = 406;
                $response->getBody()->write(json_encode(['msg' => 'Categoría inválida. Use: accion, logro, impedimento, comentario u otro']));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function delete(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $controller = new RetroItemController();
            $controller->borrarItem($id);
            $response->getBody()->write(json_encode(['msg' => 'Item eliminado correctamente']));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Item {$args['id']} no encontrado"]));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}