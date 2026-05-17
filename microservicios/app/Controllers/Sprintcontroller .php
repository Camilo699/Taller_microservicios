<?php

namespace App\Controllers;

use App\Models\Sprint;
use Exception;

class SprintController
{
    function getSprints()
    {
        /** @var \Illuminate\Database\Eloquent\Collection<Sprint> $sprints */
        $sprints = Sprint::all();
        return $sprints;
    }

    function guardarSprint($data)
    {
        if (empty($data['nombre']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            throw new Exception("Faltan campos obligatorios: nombre, fecha_inicio o fecha_fin", 1);
        }

        /** @var Sprint $sprint */
        $sprint = new Sprint();
        $sprint->nombre       = $data['nombre'];
        $sprint->fecha_inicio = $data['fecha_inicio'];
        $sprint->fecha_fin    = $data['fecha_fin'];
        $sprint->save();

        return $sprint;
    }

    function getSprint($id)
    {
        /** @var Sprint|null $sprint */
        $sprint = Sprint::find($id);
        if (empty($sprint)) {
            throw new Exception("Sprint $id no existe", 2);
        }
        return $sprint;
    }

    function modificarSprint($id, $data)
    {
        $sprint = $this->getSprint($id);

        $sprint->nombre       = $data['nombre']       ?? $sprint->nombre;
        $sprint->fecha_inicio = $data['fecha_inicio'] ?? $sprint->fecha_inicio;
        $sprint->fecha_fin    = $data['fecha_fin']    ?? $sprint->fecha_fin;
        $sprint->save();

        return $sprint;
    }

    function borrarSprint($id)
    {
        $sprint = $this->getSprint($id);
        $sprint->delete();
        return true;
    }
}