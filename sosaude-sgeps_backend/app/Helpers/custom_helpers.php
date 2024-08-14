<?php

use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;

// FILES
// validate files
if(! function_exists('validate_files')) {
    function validate_files($request, $field)
    {
        if ($request->hasFile($field)) {

            foreach ($request->{$field} as $field) {
                if (!$field->isValid()) {
                    return 0;
                }
            }

            return 1;
        }

        return 0;
    }
}
// upload file
if(! function_exists('upload_file')) {
    function upload_file($path, $file)
    {
        $extensao = $file->getClientOriginalExtension();
        $file_name = rand(1000000, 1000000000) . '.' . $extensao;
        $upload = $file->storeAs($path, $file_name);

        if (!$upload) {
            return null;
        } else {
            return basename($upload);
        }
    }
}


// upload file S3
if(! function_exists('upload_file_s3')) {
    function upload_file_s3($path, $file)
    {
        $extensao = $file->getClientOriginalExtension();
        $file_name = rand(1000000, 1000000000) . '.' . $extensao;
        $path = $path.$file_name;

        Storage::disk('s3')->put($path, file_get_contents($file));
        return basename($path);
    }
}


// download files
if (!function_exists('download_file')) {
    function download_file($file_path)
    {
        if (Storage::exists($file_path)) {
            return response()->download(storage_path('app/public/' . $file_path));
        } else {
            return response()->json(Helper::makeError('Ficheiro não localizado!'), 404);
        }
    }
}


// download files S3
if (!function_exists('download_file_s3')) {
    function download_file_s3($file_path)
    {
        return Storage::disk('s3')->response($file_path);
    }
}


// main storage path
if(! function_exists('main_storage_path')) {
    function main_storage_path()
    {
        return config('custom.s3.main_storage_path');
    }
}

// main storage path clientes
if(! function_exists('stogare_path_clientes')) {
    function stogare_path_clientes()
    {
        return config('custom.s3.stogare_path_clientes');
    }
}

// storage path empresa
if(! function_exists('storage_path_empresa')) {
    function storage_path_empresa($empresa_nome, $empresa_id, $segmento = null)
    {
        $path = null;
        $empresa = kebab_case($empresa_nome).'-'.$empresa_id;

        if(is_null($segmento)) {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_empresa')."$empresa/";
        }else {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_empresa')."$empresa/$segmento/";
        }

        return $path;
    }
}


// storage path farmacia
if(! function_exists('storage_path_farmacia')) {
    function storage_path_farmacia($farmacia_nome, $farmacia_id, $segmento = null)
    {
        $path = null;
        $farmacia = kebab_case($farmacia_nome).'-'.$farmacia_id;

        if(is_null($segmento)) {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_farmacia')."$farmacia/";
        }else {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_farmacia')."$farmacia/$segmento/";
        }

        return $path;
    }
}


// storage path unidades sanitarias
if(! function_exists('storage_path_u_sanitaria')) {
    function storage_path_u_sanitaria($unidade_sanitaria_nome, $unidade_sanitaria_id, $segmento = null)
    {
        $path = null;
        $unidade_sanitaria = kebab_case($unidade_sanitaria_nome).'-'.$unidade_sanitaria_id;

        if(is_null($segmento)) {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_u_sanitaria')."$unidade_sanitaria/";
        }else {
            $path = config('custom.s3.main_storage_path').config('custom.s3.storage_path_u_sanitaria')."$unidade_sanitaria/$segmento/";
        }

        return $path;
    }
}


// aws_url
if(! function_exists('aws_url')) {
    function aws_url()
    {
        return config('custom.s3.aws_url');
    }
}


// Delete file
if(! function_exists('delete_file')) {
    function delete_file($file_path)
    {
        if ($file_path && Storage::exists($file_path)) {
            if (Storage::delete($file_path)) {
                return 1;
            }
        }
        return 0;
    }
}

// Delete file
if(! function_exists('delete_file_s3')) {
    function delete_file_s3($file_path)
    {
        if(Storage::disk('s3')->has($file_path)) {
            Storage::disk('s3')->delete($file_path);
            return 1;
        }
        return 0;
    }
}

// Sort array of objects
if(! function_exists('sort_array_objects')) {
    function sort_desc_array_objects($key) {
        return function ($lt, $rt) use ($key) {
            if($lt->{$key} > $rt->{$key}) {
                return -1;
            } else if($lt->{$key} < $rt->{$key}) {
                return 1;
            } else {
                return 0;
            }
        };
    }
}


// check if a string begin with a given string
if(! function_exists('begin_with')) {
    function begin_with($string, $begin_string) {
        $len = strlen($begin_string);
        return (substr($string, 0, $len) === $begin_string);
    }
}


// Converting strings from Request in form-data to ...
// convertin string to boolean
if(!function_exists('to_boolean')) {
    function to_boolean($field) {
        if(!isset($field))
            return null;
        return filter_var($field, FILTER_VALIDATE_BOOLEAN);
    }
}

// convertin string to integer
if(!function_exists('to_integer')) {
    function to_integer($field) {

        if ($field == 'null' || !isset($field)) {
            $field = null;
        } else if (is_numeric($field)) {
            $field = (int) $field;
        } else {
            return $field;
        }

        return $field;
    }
}
