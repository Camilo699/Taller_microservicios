<?php

namespace App\Presentation\Repositories;

use App\Controllers\SprintController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SprintRepository
{

function list(Request $request, Response $response)
{
    try {
        $controller = new SprintController();
        $sprints = $controller->getSprints();
        $response->getBody()->write($sprints->toJson());
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    } catch (\Throwable $ex) {
        $response->getBody()->write(json_encode([
            'msg'   => $ex->getMessage(),
            'file'  => $ex->getFile(),
            'line'  => $ex->getLine()
        ]));
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }
}
 /*   function list(Request $request, Response $response)
    {
        try {
            $controller = new SprintController();
            $sprints = $controller->getSprints();
            $response->getBody()->write($sprints->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode(['msg' => 'Error al obtener los sprints']));
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }
*/ 
    function create(Request $request, Response $response)
    {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            $controller = new SprintController();
            $sprint = $controller->guardarSprint($data);
            $response->getBody()->write($sprint->toJson());
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 1) {
                $code = 406;
                $response->getBody()->write(json_encode(['msg' => 'Datos incorrectos: nombre, fecha_inicio y fecha_fin son obligatorios']));
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
            $controller = new SprintController();
            $sprint = $controller->getSprint($id);
            $response->getBody()->write($sprint->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Sprint {$args['id']} no encontrado"]));
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
            $controller = new SprintController();
            $sprint = $controller->modificarSprint($id, $data);
            $response->getBody()->write($sprint->toJson());
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Sprint {$args['id']} no encontrado"]));
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
            $controller = new SprintController();
            $controller->borrarSprint($id);
            $response->getBody()->write(json_encode(['msg' => 'Sprint eliminado correctamente']));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $ex) {
            $code = 400;
            if ($ex->getCode() == 2) {
                $code = 404;
                $response->getBody()->write(json_encode(['msg' => "Sprint {$args['id']} no encontrado"]));
            } else {
                $response->getBody()->write(json_encode(['msg' => 'Error en el servicio']));
            }
            return $response
                ->withStatus($code)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}