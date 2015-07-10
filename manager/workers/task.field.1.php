<?php

foreach($field as $k => $v) {
    // Remove asset field value and data
    if(isset($v['remove']) && $v['type'][0] === 'f') {
        File::open(SUBSTANCE . DS . $v['remove'])->delete();
        Notify::success(Config::speak('notify_file_deleted', '<code>' . $v['remove'] . '</code>'));
        unset($field[$k]);
    }
    // Remove empty field value
    if( ! isset($v['value']) || $v['value'] === "" || ( ! file_exists(SUBSTANCE . DS . $v['value']) && $v['type'][0] === 'f')) {
        unset($field[$k]);
    } else {
        // 1.1.3  => {"field_key":{"type":"s","value":"field value..."}}
        // 1.1.3+ => {"field_key":"field value..."}
        $field[$k] = $v['value'];
    }
}