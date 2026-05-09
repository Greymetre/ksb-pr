<?php

use Illuminate\Support\Facades\Storage;
use App\Models\Country;
use Illuminate\Support\Facades\Cache;

if (!function_exists('active_countries')) {
    /**
     * Get list of active countries (cached for performance)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function active_countries()
    {
        return Cache::remember('active_countries_list', 1440, function () {
            return Country::where('active', 'Y')
                          ->orderBy('country_name', 'asc')
                          ->get(['id', 'country_name']);
        });
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($request, $field, $path = 'master_distributors')
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path);
        }
        return null;
    }
}

if (!function_exists('updateFile')) {
    function updateFile($request, $field, $oldFile = null, $path = 'master_distributors')
    {
        if ($request->hasFile($field)) {

            if ($oldFile && Storage::exists($oldFile)) {
                Storage::delete($oldFile);
            }

            return $request->file($field)->store($path);
        }
        return $oldFile;
    }
}
