<?php

namespace App\Presentation\Repositories;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestRepository
{
    function index(Request $request, Response $response)
    {
        $response->getBody()->write(json_encode([
            "status"  => "success",
            "message" => "Microservicio de Retrospectivas Ágiles operando correctamente."
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    function sumar1(Request $request, Response $response)
    {
        $parms = $request->getQueryParams();
        $num1  = $parms['num1'];
        $num2  = $parms['num2'];
        $suma  = $num1 + $num2;
        $response->getBody()->write("La suma de $num1 + $num2 es igual a $suma");
        return $response;
    }

    function sumar2(Request $request, Response $response)
    {
        $body  = $request->getBody()->getContents();
        $data  = json_decode($body, true);
        $num1  = $data['num1'];
        $num2  = $data['num2'];
        $suma  = $num1 + $num2;
        $result = [
            'num1'      => $num1,
            'num2'      => $num2,
            'resultado' => $suma
        ];
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    function dividir(Request $request, Response $response)
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $num1 = $data['num1'];
        $num2 = $data['num2'];
        if ($num2 == 0) {
            $response->getBody()->write("No se puede dividir por cero");
            return $response->withStatus(400);
        }
        $result = [
            'num1'      => $num1,
            'num2'      => $num2,
            'resultado' => $num1 / $num2
        ];
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}