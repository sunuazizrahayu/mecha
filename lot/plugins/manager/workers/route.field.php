<?php


/**
 * Field Manager
 * -------------
 */

Route::accept($config->manager->slug . '/field', function() use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $fields = Get::state_field(null, null, array(), false);
    Config::set(array(
        'page_title' => $speak->fields . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.field.php'
    ));
    Shield::lot(array(
        'segment' => 'field',
        'files' => ! empty($fields) ? Mecha::O($fields) : false
    ))->attach('manager');
});


/**
 * Field Repairer/Igniter
 * ----------------------
 */

Route::accept(array($config->manager->slug . '/field/ignite', $config->manager->slug . '/field/repair/key:(:any)'), function($key = false) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! $key) {
        Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
            echo '<script>
(function($) {
    $.slug(\'title\', \'key\', \'_\');
})(window.Zepto || window.jQuery);
</script>';
        }, 11);
    }
    $fields = Get::state_field(null, null, array(), false);
    if($key === false) {
        $data = array(
            'key' => false,
            'title' => "",
            'type' => 't',
            'placeholder' => "",
            'value' => "",
            'description' => "",
            'scope' => 'article'
        );
        $title = Config::speak('manager.title_new_', $speak->field) . $config->title_separator . $config->manager->title;
    } else {
        if( ! isset($fields[$key])) {
            Shield::abort(); // Field not found!
        }
        $data = $fields[$key];
        $data['key_raw'] = $data['key'] = $key;
        $title = $speak->editing . ': ' . $data['title'] . $config->title_separator . $config->manager->title;
    }
    foreach($data as $k => $v) {
        $data[$k . '_raw'] = $v;
    }
    $G = array('data' => $data);
    Config::set(array(
        'page_title' => $title,
        'cargo' => 'repair.field.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty title field
        if(trim($request['title']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->title));
        }
        // Empty key field
        if(trim($request['key']) === "") {
            $request['key'] = $request['title'];
        }
        $k = Text::parse($request['key'], '->array_key', true);
        if($key === false) {
            if(isset($fields[$k])) {
                Notify::error(Config::speak('notify_exist', '<code>' . $k . '</code>'));
            }
        } else {
            unset($fields[$key]);
        }
        $fields[$k] = array(
            'title' => $request['title'],
            'type' => $request['type'],
            'value' => $request['value']
        );
        if(trim($request['placeholder']) !== "") {
            $fields[$k]['placeholder'] = $request['placeholder'];
        }
        if(trim($request['description']) !== "") {
            $fields[$k]['description'] = $request['description'];
        }
        if(isset($request['scope']) && is_array($request['scope'])) {
            $fields[$k]['scope'] = implode(',', $request['scope']);
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            ksort($fields);
            File::serialize($fields)->saveTo(STATE . DS . 'field.txt', 0600);
            Notify::success(Config::speak('notify_success_' . ($key === false ? 'created' : 'updated'), $request['title']));
            Weapon::fire(array('on_field_update', 'on_field_' . ($key === false ? 'construct' : 'repair')), array($G, $P));
            Guardian::kick($key !== $k ? $config->manager->slug . '/field' : $config->manager->slug . '/field/repair/key:' . $key);
        }
    }
    Shield::lot(array(
        'segment' => 'field',
        'id' => $key,
        'file' => Mecha::O($data)
    ))->attach('manager');
});


/**
 * Field Killer
 * ------------
 */

Route::accept($config->manager->slug . '/field/kill/key:(:any)', function($key = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $fields = Get::state_field(null, null, array(), false);
    if( ! isset($fields[$key])) {
        Shield::abort();
    } else {
        $data = $fields[$key];
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $data['title'] . $config->title_separator . $config->manager->title,
        'cargo' => 'kill.field.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => $request);
        $P['data']['key'] = $key;
        $s = $fields[$key]['title'];
        unset($fields[$key]); // delete ...
        ksort($fields);
        File::serialize($fields)->saveTo(STATE . DS . 'field.txt', 0600);
        Notify::success(Config::speak('notify_success_deleted', $s));
        Weapon::fire(array('on_field_update', 'on_field_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/field');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $data['title'] . '</strong>'));
    }
    Shield::lot(array(
        'segment' => 'field',
        'id' => $key,
        'file' => Mecha::O($data)
    ))->attach('manager');
});