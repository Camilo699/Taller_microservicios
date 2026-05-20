<?php

use App\Presentation\Repositories\TestRepository;
use App\Presentation\Repositories\SprintRepository;
use App\Presentation\Repositories\RetroItemRepository;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    $app->get('/',         [TestRepository::class, 'index']);
    $app->get('/suma',     [TestRepository::class, 'sumar1']);
    $app->post('/suma',    [TestRepository::class, 'sumar2']);
    $app->post('/dividir', [TestRepository::class, 'dividir']);

    $app->group('/sprints', function (RouteCollectorProxy $group) {
        $group->get('',         [SprintRepository::class, 'list']);
        $group->post('',        [SprintRepository::class, 'create']);
        $group->get('/{id}',    [SprintRepository::class, 'detail']);
        $group->put('/{id}',    [SprintRepository::class, 'update']);
        $group->delete('/{id}', [SprintRepository::class, 'delete']);
    });

    $app->group('/retro-items', function (RouteCollectorProxy $group) {
        $group->get('',                                [RetroItemRepository::class, 'list']);
        $group->post('',                               [RetroItemRepository::class, 'create']);
        $group->get('/{id}',                           [RetroItemRepository::class, 'detail']);
        $group->put('/{id}',                           [RetroItemRepository::class, 'update']);
        $group->delete('/{id}',                        [RetroItemRepository::class, 'delete']);
        $group->get('/sprint/{sprint_id}',             [RetroItemRepository::class, 'listPorSprint']);
        $group->get('/sprint/{sprint_id}/{categoria}', [RetroItemRepository::class, 'listPorCategoria']);
    });
};