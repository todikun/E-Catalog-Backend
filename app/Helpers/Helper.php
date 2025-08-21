<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use App\Models\Roles;

class Helper
{
    /**
     * Ambil role map dari DB sekali saja, cache 10 menit.
     */
    public static function getRoleMap(): array
    {
        return Cache::remember('role_map_name_to_id', 600, function () {
            return Roles::pluck('id', 'nama')
                ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
                ->toArray();
        });
    }
}
