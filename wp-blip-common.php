<?php
/*
 * Common functions file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

require_once 'blipapi.php';

define ('WP_BLIP', true);
define ('WP_BLIP_VERSION', '0.5.8');

if (defined ('WP_BLIP_DEBUG') && WP_BLIP_DEBUG) {
    error_reporting (E_ALL|E_STRICT|E_DEPRECATED);
    ini_set ('display_errors', true);
}

set_include_path (get_include_path() . PATH_SEPARATOR . dirname (__FILE__));

## ustalamy miejsce zapisu cache
$wp_blip_cacheroot  = dirname (__FILE__);
if (
    (!is_dir ($wp_blip_cacheroot) || !is_writeable ($wp_blip_cacheroot)) &&
    function_exists ('sys_get_temp_dir')
) {
    $wp_blip_cacheroot = sys_get_temp_dir ();
}

## zestaw pliterek w UTF-8
$wp_blip_plchars    =
    "\xc4\x84\xc4\x85". ## Ąą
    "\xc4\x86\xc4\x87". ## Ćć
    "\xc4\x98\xc4\x99". ## Ęę
    "\xc5\x81\xc5\x82". ## Łł
    "\xc5\x83\xc5\x84". ## Ńń
    "\xc3\x93\xc3\xb3". ## Óó
    "\xc5\x9a\xc5\x9b". ## Śś
    "\xc5\xbb\xc5\xbc". ## Żż
    "\xc5\xb9\xc5\xba"; ## Źź

$wp_blip_asciichars =   'AaCcEeLlNnOoSsZzZz';

## hack dla starszej wersji blipapi w WP-BlipBot
class WPBlipApi_Status extends BlipApi_Status {
    public function read () {
        $ret = parent::read ();

        if (count ($ret) >= 3 && count ($ret[2]) && strpos ($ret[0], '?') !== false) {
            $ret[0] .= '?' . http_build_query ($ret[2]);
        }

        return $ret;
    }
}
## koniec hacka

function wp_blip_debug () {
    $args = func_get_args ();
    echo '<pre>';
    foreach ($args as $arg) {
        echo htmlspecialchars (var_export ($arg, 1));
        echo "\n";
    }
    echo '</pre>';
}

function wp_blip_onerror ($e, $msg) {
    if (wp_blip_get_options ('onerror_notified') == $msg) {
        return $msg;
    }
    update_option ('wp_blip_onerror_notified', $msg);

    ## jesli nie ma podanego maila, to nic nie robimy, zwracamy ten sam komunikat
    if (!($email = wp_blip_get_options ('onerror_email'))) {
        return $msg;
    }

    $email_body = sprintf ('%s
W trakcie działania wtyczki do systemu WordPress: WP-Blip! wystąpił błąd. Może być on spowodowany błędami w samej wtyczce, działaniu serwerów Blip.pl, lub wieloma innymi rzeczami. Komunikat jaki został wygenerowany:
%s

Jeśli uważasz, że błąd dotyczy działania wtyczki, proszę prześlij mi tego maila na adres:
marcin@urzenia.net

Zareaguję tak szybko jak to tylko możliwe :)

Cały kontekst błędu jaki wystąpił:
Wyjątek: %s
Treść: %s
Kod: %s
Plik: %s
Linia: %s

%s',

        strftime ('%H:%M:%S %d-%m-%Y'),
        $msg,
        get_class ($e),
        $e->getMessage (),
        $e->getCode (),
        $e->getFile (),
        $e->getLine (),
        $e->getTraceAsString ()
    );

    $headers = array (
        'From: ' . get_bloginfo ('admin_email'),
        'Content-type: text/plain;charset=utf-8',
    );
    @mail ($email, '=?UTF-8?B?QsWCxIVkIFdQLUJsaXAh?=', $email_body, join ("\n", $headers));
    return $msg;
}

## funkcje główne
function wp_blip ($join="\n", $echo=0, $on_error='wp_blip_onerror') {
    $options = wp_blip_get_options ();
    if (!$options['login']) {
        return false;
    }

    $exc = null;
    try {
        $updates = wp_blip_cache ();

        if ($options['onerror_notified']) {
            update_option ('wp_blip_onerror_notified', '');
        }
    }
    catch (Exception $exc) {
        if (defined ('WP_BLIP_DEBUG') && WP_BLIP_DEBUG) {
            throw $exc;
        }
    }

    ## obsluga bledow
    if (!is_null ($exc) && $exc instanceof Exception) {
        $msg = null;
        if ($exc instanceof RuntimeException && $exc->getCode () == 403) {
            $msg = 'Wystąpił błąd przy próbie połączenia z serwerami Blip.pl.';
        }
        else {
            $msg = sprintf ('Wystąpił błąd: [%d] %s', $exc->getCode (), $exc->getMessage ());
        }

        if ($on_error && is_callable ($on_error)) {
            $msg = call_user_func ($on_error, $exc, $msg);
        }

        if ($echo && $msg) {
            echo $msg;
        }
        return $msg;
    }

    if ($updates === false) {
        return;
    }

    $pat        = array ('%date', '%body', '%url');
    $ret        = array ();
    $date_fun   = 'wp_blip_date_' . $options['datetype'];
    foreach ($updates as $update) {
        $rep = array (
            $date_fun ($update['created_at'], $options),
            $update['body'],
            'http://blip.pl/s/'. $update['id'],
        );

        $ret[] = str_replace ($pat, $rep, $options['tpl']);
    }

    if ($join === false) {
        return $ret;
    }

    $ret =
        $options['tpl_container_pre'] .
        join ($join, $ret) .
        $options['tpl_container_post'];

    if ($echo) {
        echo $ret;
    }

    return $ret;
}

function wp_blip_cache () {
    $options = wp_blip_get_options ();
    if (!$options['login']) {
        return false;
    }

    ## musimy miec zapisywalny katalog dla cache
    if (!is_dir ($GLOBALS['wp_blip_cacheroot']) || !is_writeable ($GLOBALS['wp_blip_cacheroot'])) {
        trigger_error ('WP-Blip! cannot write cache file in '. $GLOBALS['wp_blip_cacheroot'] .' directory.', E_USER_NOTICE);
    }

    $cachefile = _wp_blip_get_cache_filename ();

    $ret = array ();
    if (
        ## nie uzywamy cache w trybie debug
        (!defined ('WP_BLIP_DEBUG') || !WP_BLIP_DEBUG) &&
        ## nie uzywamy cache jesli user nie zdefiniowal czasu
        $options['time'] &&
        ## cache istnieje
        file_exists ($cachefile) &&
        ## cache jest aktualny
        (filemtime ($cachefile) + $options['time']) > time () &&
        ## nie wymuszamy odswiezenie, bez cache
        (!isset ($_ENV['HTTP_CACHE_CONTROL']) || strtolower ($_ENV['HTTP_CACHE_CONTROL']) != 'no-cache')
    ) {
        ## zatem  wracamy dane z cache
        return unserialize (stripslashes (file_get_contents ($cachefile)));
    }

    if (!$options['quant']) {
        $options['quant'] = 10;
        update_option ('wp_blip_quant', $options['quant']);
    }

    $bapi = wp_blip_connect ();

    ## pobieramy statusy
    $status             = new WPBlipApi_Status ();
    $status->user       = $options['login'];
    $status->limit      = (int)$options['quant'];
    $status->include    = array ('pictures');
    $statuses           = $bapi->read ($status);

    $status             = null;

    ## jeśli filtrujemy po tagach:
    if ($options['tags']) {
        ## rozdzielamy tagi
        $options['tags'] = preg_split ('!\s!', $options['tags']);

        ## odfiltrowujemy niechciane statusy
        $statuses = _wp_blip_filter_statuses_by_tags ($options['tags'], $statuses['body']);

        ## szukamy pozostałych statusów jeśli trzeba
        $offset = (int)$options['quant'];
        $tries  = 20;
        while (count ($statuses) < $options['quant'] && --$tries) {
            ## przypierwszej iteracji petli $status jest nullem (zerowane jest wyzej, przed ifem), wiec
            ## inicjalizujemy teraz po swojemu
            if (!$status) {
                $status             = new WPBlipApi_Status ();
                $status->user       = $options['login'];
                $status->include    = array ('pictures');
                $status->limit      = 20;
            }
            $status->offset         = $offset;
            $filtered               = $bapi->read ($status);

            ## jesli skonczyly sie juz statusy (pobrane sa wszystkie statusy usera) lub jakis blad, to przerywamy
            if (!is_array ($filtered['body']) || !count ($filtered['body'])) {
                break;
            }

            $filtered           = _wp_blip_filter_statuses_by_tags ($options['tags'], $filtered['body']);
            if (count ($filtered)) {
                $statuses = array_merge ($statuses, $filtered);
            }
            $offset += 20;

            ## jak za dużo to ucinamy nadmiarowe statusy
            if (count ($statuses) > $options['quant']) {
                $statuses = array_splice ($statuses, 0, $options['quant']);
            }
        }
    }
    else {
        $statuses = $statuses['body'];
    }

    $save = array ();
    foreach ($statuses as $status) {
        $date = preg_split ('#[: -]#', $status->created_at);
        $save[] = array (
            'created_at'    => mktime ($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]),
            'id'            => $status->id,
            'picture'       => (count ($status->pictures) ? $status->pictures[0]->url : ''),
            'body'            => wp_blip_linkify (
                htmlspecialchars ($status->body),
                array (
                    'expand_rdir'               => $options['expand_rdir'],
                    'expand_linked_statuses'    => $options['expand_linked_statuses'],
                )
            ),
        );

        if ($save[-1]['created_at'] < 0) {
            $save[-1]['created_at'] = 0;
        }
    }

    @file_put_contents ($cachefile, serialize ($save), LOCK_EX);
    return $save;
}


## funkcje narzędziowe
function wp_blip_connect () {
    static $bapi;

    if (is_null ($bapi)) {
        $bapi = new BlipApi ();
        $bapi->connect ();
        $bapi->uagent = 'WP Blip!/'. WP_BLIP_VERSION .' (http://wp-blip.googlecode.com)';

        if (!function_exists ('json_decode')) {
            require_once 'JSON.class.php';
            $json = new Services_JSON ();
            $bapi->parser = array ($json, 'decode');
        }
    }

    return $bapi;
}

function wp_blip_cache_invalidate () {
    $cachefile = _wp_blip_get_cache_filename ();

    if (is_file ($cachefile)) {
        @unlink ($cachefile);
    }
    return !is_file ($cachefile);
}

function wp_blip_date_absolute ($ts, $options) {
    return strftime ($options['dateformat'], $ts);
}

function wp_blip_date_relative ($ts, $options) {
    $time_diff = time () - $ts;

    if ($time_diff > (86400 * min ($options['absolute_from'], 365))) {
        return wp_blip_date_absolute ($ts, $options);
    }

    $ret = array ();
    ## miesiace
    if ($time_diff > (86400 * 30)) {
        $date       = floor ($time_diff / 86400 / 30);
        $ret[]      = _wp_blip_date_names ($date, 'm');
        $time_diff  -= $date * 86400 * 30;
    }
    ## tygodnie
    if ($time_diff > (86400 * 7)) {
        $date       = floor ($time_diff / 86400 / 7);
        $ret[]      = _wp_blip_date_names ($date, 'w');
        $time_diff  -= $date * 86400 * 7;
    }
    ## dni
    if ($time_diff > 86400) {
        $date       = floor ($time_diff / 86400);
        $ret[]      = _wp_blip_date_names ($date, 'd');
        $time_diff  -= $date * 86400;
    }
    ## godziny
    if ($time_diff > (3600)) {
        $date       = floor ($time_diff / 3600);
        $ret[]      = _wp_blip_date_names ($date, 'H');
        $time_diff  -= $date * 3600;
    }
    ## minuty
    if ($time_diff > 60) {
        $date       = floor ($time_diff / 60);
        $ret[]      = _wp_blip_date_names ($date, 'M');
        $time_diff  -= $date * 60;
    }
    ## sekundy
    if ($time_diff > 0) {
        $ret[]      = _wp_blip_date_names ($time_diff, 'S');
    }

    return join (', ', $ret);
}

function wp_blip_date_relative_simple ($ts, $options) {
    $time_diff = time () - $ts;
    if ($time_diff/86400/365 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/31536000), 'y');
    }
    elseif ($time_diff/86400/30 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/2592000), 'm');
    }
    elseif ($time_diff/86400/7 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/604800), 'w');
    }
    elseif ($time_diff/86400 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/86400), 'd');
    }
    elseif ($time_diff/3600 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/3600), 'H');
    }
    elseif ($time_diff/60 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/60), 'M');
    }
    else {
        return _wp_blip_date_names ($time_diff, 'S');
    }
}

function wp_blip_get_options ($keys = null) {
    static $options = null;

    if (is_null ($options)) {
        $options = array (
            'login'                     => '',
            'quant'                     => 10,
            'tpl'                       => '<li>(%date) %body</li>',
            'time'                      => 300,
            'tags'                      => '',
            'dateformat'                => '%Y-%m-%d %H:%M:%S',
            'datetype'                  => 'absolute',
            'tpl_container_pre'         => '<ul>',
            'tpl_container_post'        => '</ul>',
            'expand_rdir'               => false,
            'expand_linked_statuses'    => false,
            'onerror_email'             => '',
            'onerror_notified'          => '',
            'version'                   => WP_BLIP_VERSION,
            'absolute_from'             => 365,
        );

        foreach ($options as $option => &$default) {
            $value = get_option ('wp_blip_'.$option);
            if ($value !== false) {
                $default = $value;
            }
        }

        if (get_option ('wp_blip_password') !== false) {
            delete_option ('wp_blip_password');
        }

        $version = get_option ('wp_blip_version');
        if ($version === false || $version != WP_BLIP_VERSION) {
            wp_blip_version_change ($version);
            update_option ('wp_blip_version', WP_BLIP_VERSION);
        }
    }

    if (is_string ($keys)) {
        return $options[$keys];
    }
    else if (is_array ($keys)) {
        $ret = array ();
        foreach ($keys as $key) {
            if (!isset ($options[$key])) {
                continue;
            }
            $ret[$key] = $options[$key];
        }
        return $ret;
    }

    return $options;
}

function wp_blip_linkify ($status, $opts = array ()) {
    if (!$status || gettype ($status) != 'string') {
        return $status;
    }

    return preg_replace_callback (
        '!
        (?:
            (?:
                https?://
                (?:www.)?
                (
                    blip\.pl/[a-z0-9_-]+/[a-z0-9_-]+
                    |
                    rdir\.pl/[a-z0-9_-]+
                )
            )
            |
            (?:[#^][-a-z0-9_'.$GLOBALS['wp_blip_plchars'].']+)
        )
        !xi',
        '_wp_blip_linkify__callback',
        $status
    );
}

function wp_blip_utf2ascii ($str) {
    return str_replace (
        str_split ($GLOBALS['wp_blip_plchars'], 2),
        str_split ($GLOBALS['wp_blip_asciichars']),
        $str
    );
}

function wp_blip_version_change ($old_version) {
    foreach (glob ($GLOBALS['wp_blip_cacheroot'] .'/wp_blip.*.cache.txt') as $file) {
        @unlink ($file);
    }
}


## funkcje pomocnicze (callbacki)
function _wp_blip_date_names ($ts, $unit) {
    static $units = array (
        'S' => array ('sekundę',    'sekundy',  'sekund'),
        'M' => array ('minutę',     'minuty',   'minut'),
        'H' => array ('godzinę',    'godziny',  'godzin'),
        'd' => array ('dzień',      'dni',      'dni'),
        'w' => array ('tydzień',    'tygodnie', 'tygodni'),
        'm' => array ('miesiąc',    'miesiące', 'miesięcy'),
        'y' => array ('rok',        'lata',     'lat')
    );

    if ($ts == 1) {
        return 1 .' '. $units[$unit][0];
    }

    $ts = $ts % 100;
    if (
        (
            ($ts % 10) > 1 &&
            ($ts % 10) < 5
        )
        &&
        (
            ($ts < 11) ||
            ($ts > 15)
        )
    ) {
        return $ts .' '. $units[$unit][1];
    }
    else {
        return $ts .' '. $units[$unit][2];
    }
}

function _wp_blip_filter_statuses_by_tags ($tags, $statuses) {
    $filtered = array ();
    foreach ($statuses as $status) {
        foreach (_wp_blip_find_tags ($status->body) as $tag) {
            if (in_array ($tag, $tags)) {
                $filtered[] = $status;
                continue 2;
            }
        }
    }

    return $filtered;
}

function _wp_blip_find_tags ($status) {
    $status = str_replace (array ('-', '_'), '', $status);
    preg_match_all ('!#([a-zA-Z0-9'.$GLOBALS['wp_blip_plchars'].']+)!', $status, $statuses);
    return $statuses[1];
}

function _wp_blip_get_cache_filename () {
    $options = wp_blip_get_options ();

    return
        $GLOBALS['wp_blip_cacheroot'] .'/wp_blip.'.
        ($options['login'] ? $options['login'] : '') .'_'.
        ($options['tags'] ? $options['tags'] : '') .'_'.
        $options['quant'] .'.cache.txt';
}

function _wp_blip_linkify__callback ($match) {
    $opts = wp_blip_get_options ();

    if ($match[0][0] == '^') {
        return '<a href="http://'. substr ($match[0], 1) .'.blip.pl/">'. $match[0] .'</a>';
    }
    else if ($match[0][0] == '#') {
        return '<a href="http://blip.pl/tags/'. wp_blip_utf2ascii (substr ($match[0], 1)) .'">'. $match[0] .'</a>';
    }
    else if (!isset ($match[1])) {
        return $match[0];
    }
    else if (substr ($match[1], 0, 4) == 'rdir') {
        $link_href = $match[0];

        if ($opts['expand_rdir']) {
            $link       = explode ('/', $match[1]);

            try {
                $bapi           = wp_blip_connect ();
                $shlink         = new BlipApi_Shortlink ();
                $shlink->code   = $link[1];
                $link_data      = $bapi->read ($shlink);
                if ($link_data['status_code'] == 200) {
                    $link_href = $link_data['body']->original_link;
                }
            } catch (RuntimeException $e) {
            }
        }

        return '<a href="'.$link_href.'" title="'.$match[0].'">'.$link_href.'</a>';
    }
    else if (substr ($match[1], 0, 4) == 'blip') {
        $link       = explode ('/', $match[1]);
        $title      = '';

        if ($opts['expand_linked_statuses'] && $link[1] == 's') {
            try {
                $bapi       = wp_blip_connect ();
                $status     = new BlipApi_Status ();
                $status->id = (int)$link[2];
                $st_data    = $bapi->read ($status);
                if ($st_data['status_code'] == 200) {
                    $title = explode ('/', $st_data['body']->user_path);
                    $title = htmlspecialchars ('^'. $title[2] .': '. $st_data['body']->body);
                }
            } catch (RuntimeException $e) {
            }
        }

        return '<a href="'.$match[0].'" title="'. $title .'">'.$match[0].'</a>';
    }

    return $match[0];
}

