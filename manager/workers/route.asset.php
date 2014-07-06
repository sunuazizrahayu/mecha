﻿<?php


/**
 * Assets Manager
 * --------------
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {
    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        File::upload($_FILES['file'], ASSET);
        $P = array('data' => $_FILES);
        Weapon::fire('on_asset_update', array($P, $P));
        Weapon::fire('on_asset_construct', array($P, $P));
    }
    $takes = Get::files(ASSET, '*', 'DESC', 'update');
    if($_files = Mecha::eat($takes)->chunk($offset, $config->per_page * 2)->vomit()) {
        $files = array();
        foreach($_files as $_file) $files[] = $_file;
    } else {
        $files = false;
    }
    Config::set(array(
        'page_title' => $speak->assets . $config->title_separator . $config->manager->title,
        'files' => $files,
        'pagination' => Navigator::extract($takes, $offset, $config->per_page * 2, $config->manager->slug . '/asset'),
        'cargo' => DECK . DS . 'workers' . DS . 'asset.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Asset Killer
 * ------------
 */

Route::accept($config->manager->slug . '/asset/kill/files?:(:all)', function($name = "") use($config, $speak) {
    $name = str_replace(array('\\', '/'), DS, $name);
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if(strpos($name, ';') !== false) {
        $deletes = explode(';', $name);
    } else {
        if( ! File::exist(ASSET . DS . $name)) {
            Shield::abort(); // file not found!
        } else {
            $deletes = array($name);
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . (count($deletes) === 1 ? basename($deletes[0]) : $speak->assets) . $config->title_separator . $config->manager->title,
        'name' => $deletes,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.asset.php'
    ));
    if(Request::post()) {
        Guardian::checkToken(Request::post('token'));
        $info_path = array();
        foreach($deletes as $file_to_delete) {
            $_path = ASSET . DS . $file_to_delete;
            $info_path[] = $_path;
            File::open($_path)->delete();
        }
        $P = array('data' => array('type' => $path, 'file' => $info_path[0], 'files' => $info_path));
        Notify::success(Config::speak('notify_file_deleted', array('<code>' . implode('</code>, <code>', $deletes) . '</code>')));
        Weapon::fire('on_asset_update', array($P, $P));
        Weapon::fire('on_asset_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/asset');
    } else {
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::attach('manager', false);
});


/**
 * Multiple Asset Killer
 * ---------------------
 */

Route::accept($config->manager->slug . '/asset/kill', function($path = "") use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        if( ! isset($request['selected'])) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/asset');
        }
        Guardian::kick($config->manager->slug . '/asset/kill/files:' . implode(';', $request['selected']));
    }
});


/**
 * Asset Repair
 * ------------
 */

Route::accept($config->manager->slug . '/asset/repair/files?:(:all)', function($old = "") use($config, $speak) {
    $dir_name = rtrim(dirname(str_replace(array('\\', '/'), DS, $old)), '\\/');
    $old_name = ltrim(basename($old), '\\/');
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if( ! $file = File::exist(ASSET . DS . $dir_name . DS . $old_name)) {
        Shield::abort(); // file not found!
    }
    Config::set(array(
        'page_title' => $speak->editing . ': ' . $old_name . $config->title_separator . $config->manager->title,
        'name' => $old_name,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty field
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->name)));
        } else {
            // Missing file extension
            if( ! preg_match('#^.*?\.(.+?)$#', $request['name'])) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            // Safe file name
            $name = explode('.', $request['name']);
            $parts = array();
            foreach($name as $part) {
                $parts[] = Text::parse($part)->to_slug_moderate;
            }
            $new_name = implode('.', $parts);
            // File name already exist
            if($old_name !== $new_name && File::exist(dirname($file) . DS . $new_name)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $new_name . '</code>')));
            }
            $P = array('data' => array('path' => $file, 'name_old' => $old_name, 'name_new' => $new_name));
            if( ! Notify::errors()) {
                if(Request::post('content')) {
                    File::open($file)->write($request['content'])->save();
                }
                File::open($file)->renameTo($new_name);
                Notify::success(Config::speak('notify_file_updated', array('<code>' . $old_name . '</code>')));
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_repair', array($P, $P));
                Guardian::kick($config->manager->slug . '/asset');
            }
        }
    } else {
        Guardian::memorize(array('name' => $old_name));
    }
    Shield::attach('manager', false);
});