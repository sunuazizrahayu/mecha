<?php


/**
 * Include Comment, Custom CSS + JS to the Article Data
 * ----------------------------------------------------
 */

Filter::add('shield:lot', function($data) {
    $config = Config::get();
    $speak = Config::speak();
    if(isset($data[$config->page_type]) && is_object($data[$config->page_type])) {
        $results = $data[$config->page_type];
        $FP = $config->page_type . ':';
        // Include comment(s) data
        if($comments = Get::comments('ASC', 'post:' . Date::slug($results->id), (Guardian::happy() ? 'txt,hold' : 'txt'), COMMENT)) {
            $results->comments = array();
            $results->total_comments = Get::AMF($comments !== false ? count($comments) : 0, $FP, 'total_comments');
            $results->total_comments_text = Get::AMF($results->total_comments . ' ' . ($results->total_comments === 1 ? $speak->comment : $speak->comments), $FP, 'total_comments_text');
            foreach($comments as $comment) {
                $results->comments[] = Get::comment($comment, array(), array(COMMENT, ARTICLE), '/' . $config->index->slug . '/', 'comment:');
            }
            $results->comments = Get::AMF($results->comments, $FP, 'comments');
            unset($comments);
        }
        $results->total_comments = Get::AMF($comments ? count($comments) : 0, $FP, 'total_comments');
        $results->total_comments_text = Get::AMF($results->total_comments . ' ' . ($results->total_comments === 1 ? $speak->comment : $speak->comments), $FP, 'total_comments_text');
        // Include custom CSS and JS data
        $results->css = $results->js = $results->css_raw = $results->js_raw = "";
        if($file = File::exist(CUSTOM . DS . Date::slug($results->time) . '.' . File::E($results->path))) {
            $custom = explode(SEPARATOR, File::open($file)->read());
            $css = isset($custom[0]) ? Text::DS(trim($custom[0])) : "";
            $js = isset($custom[1]) ? Text::DS(trim($custom[1])) : "";
            // css_raw
            // page:css_raw
            // custom:css_raw
            // shortcode
            // page:shortcode
            // custom:shortcode
            // css
            // page:css
            // custom:css
            $css = Get::AMF($css, $FP, 'css_raw');
            $results->css_raw = Filter::apply('custom:css_raw', $css);
            $css = Get::AMF($css, $FP, 'shortcode');
            $css = Filter::apply('custom:shortcode', $css);
            $css = Get::AMF($css, $FP, 'css');
            $results->css = Filter::apply('custom:css', $css);
            // js_raw
            // page:js_raw
            // custom:js_raw
            // shortcode
            // page:shortcode
            // custom:shortcode
            // js
            // page:js
            // custom:js
            $js = Get::AMF($js, $FP, 'js_raw');
            $results->js_raw = Filter::apply('custom:js_raw', $js);
            $js = Get::AMF($js, $FP, 'shortcode');
            $js = Filter::apply('custom:shortcode', $js);
            $js = Get::AMF($js, $FP, 'js');
            $results->js = Filter::apply('custom:js', $js);
        }
        $data[$config->page_type] = $results;
        unset($results);
    }
    return $data;
}, 1);


/**
 * ==========================================================================
 *  GET PAGE/ARTICLE PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pagePath('lorem-ipsum'));
 *    var_dump(Get::articlePath('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type  | Description
 *  --------- | ----- | -----------------------------------------------------
 *  $detector | mixed | Slug, ID or time of the article
 *  --------- | ----- | -----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('pagePath', function($detector) {
    return Get::postPath($detector, PAGE);
});

Get::plug('articlePath', function($detector) {
    return Get::postPath($detector, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF PAGE/ARTICLE DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageExtract($input));
 *    var_dump(Get::articleExtract($input));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageExtract', function($input) {
    return Get::postExtract($input, 'page:');
});

Get::plug('articleExtract', function($input) {
    return Get::postExtract($input, 'article:');
});


/**
 * ==========================================================================
 *  GET LIST OF PAGE(S)/ARTICLE(S) PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::pages() as $path) { ... }
 *    foreach(Get::articles() as $path) { ... }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pages', function($order = 'DESC', $filter = "", $e = 'txt') {
    return Get::posts($order, $filter, $e, PAGE);
});

Get::plug('articles', function($order = 'DESC', $filter = "", $e = 'txt') {
    return Get::posts($order, $filter, $e, ARTICLE);
});


/**
 * ==========================================================================
 *  GET LIST OF PAGES(S)/ARTICLE(S) DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::pagesExtract() as $file) { ... }
 *    foreach(Get::articlesExtract() as $file) { ... }
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pagesExtract', function($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::postsExtract($order, $sorter, $filter, $e, 'page:', PAGE);
});

Get::plug('articlesExtract', function($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::postsExtract($order, $sorter, $filter, $e, 'article:', ARTICLE);
});


/**
 * ==========================================================================
 *  GET MINIMUM DATA OF A PAGE/ARTICLE
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageAnchor('lorem-ipsum'));
 *    var_dump(Get::articleAnchor('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageAnchor', function($path) {
    return Get::postAnchor($path, PAGE, '/', 'page:');
});

Get::plug('articleAnchor', function($path) {
    return Get::postAnchor($path, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  GET PAGE/ARTICLE HEADER(S) ONLY
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::pageHeader('lorem-ipsum'));
 *    var_dump(Get::articleHeader('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('pageHeader', function($path) {
    return Get::postHeader($path, PAGE, '/', 'page:');
});

Get::plug('articleHeader', function($path) {
    return Get::postHeader($path, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  EXTRACT PAGE/ARTICLE FILE INTO LIST OF PAGE/ARTICLE DATA
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::page('lorem-ipsum'));
 *    var_dump(Get::article('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('page', function($reference, $excludes = array()) {
    return Get::post($reference, $excludes, PAGE, '/', 'page:');
});

Get::plug('article', function($reference, $excludes = array()) {
    return Get::post($reference, $excludes, ARTICLE, '/' . Config::get('index.slug') . '/', 'article:');
});


/**
 * ==========================================================================
 *  GET COMMENT PATH
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentPath('lorem-ipsum'));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type  | Description
 *  --------- | ----- | -----------------------------------------------------
 *  $detector | mixed | Slug, ID or time of the page
 *  --------- | ----- | -----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('commentPath', function($detector) {
    return Get::responsePath($detector, COMMENT);
});


/**
 * ==========================================================================
 *  GET LIST OF COMMENT DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::commentExtract($input));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('commentExtract', function($input) {
    return Get::responseExtract($input, 'comment:');
});


/**
 * ===========================================================================
 *  GET LIST OF COMMENT(S) PATH
 * ===========================================================================
 *
 * -- CODE: ------------------------------------------------------------------
 *
 *    foreach(Get::comments() as $path) {
 *        echo $path . '<br>';
 *    }
 *
 * ---------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type    | Description
 *  --------- | ------- | ----------------------------------------------------
 *  $order    | string  | Ascending or descending? ASC/DESC?
 *  $filter   | string  | The result(s) filter
 *  $e        | boolean | The file extension(s)
 *  --------- | ------- | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('comments', function($order = 'ASC', $filter = "", $e = 'txt') {
    return Get::responses($order, $filter, $e, COMMENT);
});


/**
 * ==========================================================================
 *  GET LIST OF COMMENT(S) DETAIL(S)
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    foreach(Get::commentsExtract() as $file) {
 *        echo $file['path'] . '<br>';
 *    }
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------------
 *  $sorter   | string | The key of array item as sorting reference
 *  --------- | ------ | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('commentsExtract', function($order = 'ASC', $sorter = 'time', $filter = "", $e = 'txt') {
    return Get::responsesExtract($order, $sorter, $filter, $e, 'comment:', COMMENT);
});


/**
 * ==========================================================================
 *  EXTRACT COMMENT FILE INTO LIST OF COMMENT DATA FROM ITS PATH/ID/TIME
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::comment(1399334470));
 *
 * --------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter  | Type   | Description
 *  ---------- | ------ | ---------------------------------------------------
 *  $reference | string | Comment path, ID or time
 *  $excludes  | array  | Exclude some field(s) from result(s)
 *  ---------- | ------ | ---------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

Get::plug('comment', function($reference, $excludes = array()) {
    return Get::response($reference, $excludes, array(COMMENT, ARTICLE), '/' . Config::get('index.slug') . '/', 'comment:');
});


/**
 * ==========================================================================
 *  GET CLIENT IP ADDRESS
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    echo Get::IP();
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('IP', function() {
    $ip = 'N/A';
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
            $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($addresses[0]);
        } else {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return Guardian::check($ip, '->ip') ? $ip : 'N/A';
});


/**
 * ==========================================================================
 *  GET CLIENT USER AGENT INFO
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    echo Get::UA();
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('UA', function() {
    return $_SERVER['HTTP_USER_AGENT'];
});


/**
 * ==========================================================================
 *  GET TIMEZONE LIST
 * ==========================================================================
 *
 * -- CODE: -----------------------------------------------------------------
 *
 *    var_dump(Get::timezone());
 *    var_dump(Get::timezone('Asia/Jakarta'));
 *
 * --------------------------------------------------------------------------
 *
 */

Get::plug('timezone', function($identifier = null, $fallback = false, $format = '(UTC%1$s) %2$s &ndash; %3$s') {
    // http://pastebin.com/vBmW1cnX
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC
    );
    $timezones = array();
    $timezone_offsets = array();
    foreach($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }
    foreach($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }
    $a = $b = array();
    foreach($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));
        $pretty_offset = $offset_prefix . $offset_formatted;
        $t = new DateTimeZone($timezone);
        $c = new DateTime(null, $t);
        $current_time = $c->format('g:i A');
        $text = sprintf($format, $pretty_offset, str_replace('_', ' ', $timezone), $current_time);
        if($offset < 0) {
            $b[$timezone] = $text;
        } else {
            $a[$timezone] = $text;
        }
    }
    asort($a);
    arsort($b);
    $timezone_list = $b + $a;
    if( ! is_null($identifier)) {
        return isset($timezone_list[$identifier]) ? $timezone_list[$identifier] : $fallback;
    }
    return $timezone_list;
});


// DEPRECATED. Please use `Mecha::A(Get::tags())`
Get::plug('rawTags', function($order = 'ASC', $sorter = 'name') {
    return Mecha::A(Get::tags($order, $sorter));
});

// DEPRECATED. Please use `Mecha::A(Get::tag())`
Get::plug('rawTag', function($filter, $output = null, $fallback = false) {
    return Mecha::A(Get::tag($filter, $output, $fallback));
});

// DEPRECATED. Please use `Converter::curt()`
Get::plug('summary', function($input, $chars = 100, $tail = '&hellip;', $charset = "") {
    return Converter::curt($input, $chars, $tail, $charset);
});