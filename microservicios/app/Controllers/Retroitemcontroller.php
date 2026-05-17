<?php

namespace App\Controllers;

use App\Models\RetroItem;
use Exception;

class RetroItemController
{
    private $categoriasValidas = ['accion', 'logro', 'impedimento', 'comentario', 'otro'];

    function getItems()
    {
        return RetroItem::all();
    }

    function getItemsPorSprint($sprint_id)
    {
        $items = RetroItem::where('sprint_id', $sprint_id)->get();
        if ($items->isEmpty()) {
            throw new Exception("No hay items para el sprint $sprint_id", 2);
        }
        return $items;
    }

    function getItemsPorCategoria($sprint_id, $categoria)
    {
        if (!in_array($categoria, $this->categoriasValidas)) {
            throw new Exception("Categoría inválida: $categoria", 3);
        }

        return RetroItem::where('sprint_id', $sprint_id)
            ->where('categoria', $categoria)
            ->get();
    }

    function guardarItem($data)
    {
        if (empty($data['sprint_id']) || empty($data['categoria']) || empty($data['descripcion'])) {
            throw new Exception("Faltan campos obligatorios: sprint_id, categoria o descripcion", 1);
        }

        if (!in_array($data['categoria'], $this->categoriasValidas)) {
            throw new Exception("Categoría inválida: {$data['categoria']}", 3);
        }

        $item = new RetroItem();
        $item->sprint_id      = $data['sprint_id'];
        $item->categoria      = $data['categoria'];
        $item->descripcion    = $data['descripcion'];
        $item->cumplida       = $data['categoria'] === 'accion' ? ($data['cumplida'] ?? null) : null;
        $item->fecha_revision = empty($data['fecha_revision']) ? null : $data['fecha_revision'];
        $item->save();

        return $item;
    }

    function getItem($id)
    {
        $item = RetroItem::find($id);
        if (empty($item)) {
            throw new Exception("Item $id no existe", 2);
        }
        return $item;
    }

    function modificarItem($id, $data)
    {
        $item = $this->getItem($id);

        if (!empty($data['categoria']) && !in_array($data['categoria'], $this->categoriasValidas)) {
            throw new Exception("Categoría inválida: {$data['categoria']}", 3);
        }

        $item->categoria      = $data['categoria']      ?? $item->categoria;
        $item->descripcion    = $data['descripcion']    ?? $item->descripcion;
        $item->fecha_revision = $data['fecha_revision'] ?? $item->fecha_revision;

        // cumplida solo aplica para acciones
        if ($item->categoria === 'accion') {
            $item->cumplida = $data['cumplida'] ?? $item->cumplida;
        } else {
            $item->cumplida = null;
        }

        $item->save();
        return $item;
    }

    function borrarItem($id)
    {
        $item = $this->getItem($id);
        $item->delete();
        return true;
    }
}