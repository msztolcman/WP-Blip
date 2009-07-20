<?php
/*
 * Common functions file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

define ('WP_BLIP', true);

set_include_path (get_include_path() . PATH_SEPARATOR . dirname (__FILE__));

$wp_blip_cacheroot  = dirname (__FILE__);
if (
    (!is_dir ($wp_blip_cacheroot) || !is_writeable ($wp_blip_cacheroot)) &&
    function_exists ('sys_get_temp_dir')
) {
    $wp_blip_cacheroot = sys_get_temp_dir ();
}

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

function wp_blip_debug () {
    $args = func_get_args ();
    echo '<pre>';
    foreach ($args as $arg) {
        echo htmlspecialchars (print_r ($arg, 1));
        echo "\n";
    }
    echo '</pre>';
}

## funkcje główne
function wp_blip ($join="\n", $echo=0) {
    $options = wp_blip_get_options ();
	if (!$options['login']) {
		return false;
	}

	$updates = wp_blip_cache ();

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

    if (!is_dir ($GLOBALS['wp_blip_cacheroot']) || !is_writeable ($GLOBALS['wp_blip_cacheroot'])) {
        trigger_error ('WP-Blip! cannot write cache file in '. $GLOBALS['wp_blip_cacheroot'] .' directory.', E_USER_NOTICE);
    }

	$cachefile = _wp_blip_get_cache_filename ();

	$ret = array ();
    if (
        (!defined ('WP_BLIP_DEBUG') || !WP_BLIP_DEBUG) &&
        $options['time'] &&
        file_exists ($cachefile) &&
        (filemtime ($cachefile) + $options['time']) > time () &&
        (!isset ($_ENV['HTTP_CACHE_CONTROL']) || strtolower ($_ENV['HTTP_CACHE_CONTROL']) != 'no-cache')
    ) {

		return unserialize (stripslashes (file_get_contents ($cachefile)));
	}
	else {
		if (!$options['quant']) {
			$options['quant'] = 10;
			update_option ('wp_blip_quant', $options['quant']);
		}

        $bapi = wp_blip_connect ();

        ## pobieramy statusy
		$statuses = $bapi->status_read (null, $options['login'], array (), false, $options['quant']);

        ## jeśli filtrujemy po tagach:
        if ($options['tags']) {
            ## rozdzielamy tagi
            $options['tags'] = preg_split ('!\s!', $options['tags']);

            ## odfiltrowujemy niechciane statusy
            $statuses = _wp_blip_filter_statuses_by_tags ($options['tags'], $statuses['body']);

            ## szukamy pozostałych statusów jeśli trzeba
            $offset = $options['quant'];
            while (count ($statuses) < $options['quant']) {
		        $filtered = $bapi->status_read (null, $options['login'], array (), false, 20, $offset);
                $filtered = _wp_blip_filter_statuses_by_tags ($options['tags'], $filtered['body']);
                if (count ($filtered)) {
                    $statuses = array_merge ($statuses, $filtered);
                }
                $offset += 20;
            }

            ## jak za dużo to ucinamy nadmiarowe statusy
            if (count ($statuses) > $options['quant']) {
                $statuses = array_splice ($statuses, 0, $options['quant']);
            }
        }
        else {
            $statuses = $statuses['body'];
        }

		$save = array ();
		foreach ($statuses as $status) {
            $date = preg_split ('#[: -]#', $status->created_at);
			$save[] = array (
				'created_at'	=> mktime ($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]),
                'body'			=> wp_blip_linkify (
                    htmlspecialchars ($status->body),
                    array (
                        'expand_rdir'               => $options['expand_rdir'],
                        'expand_linked_statuses'    => $options['expand_linked_statuses'],
                    )
                ),
				'id'			=> $status->id,
			);
		}

		@file_put_contents ($cachefile, serialize ($save), LOCK_EX);
		return $save;
	}
}


## funkcje narzędziowe
function wp_blip_connect () {
    static $bapi;

    if (is_null ($bapi)) {
		require_once 'blipapi.php';

        if (!function_exists ('json_decode') || 1) {
            require_once 'JSON.class.php';
            ## brzydki hack - dotychczas w blipapi.php nie dalo sie latwo zmienic funkcji parsujacej json
            ## w nowej wersji bedzie to poprawione, i ten hack nie bedzie potrzebny
            class WPBlipAPI extends BlipApi {
                public function __construct ($login=null, $passwd=null, $dont_connect=false) {
                    $json = new Services_JSON ();
                    $this->_parsers['application/json'] = array ($json, 'decode');

                    return parent::__construct ($login, $passwd, $dont_connect);
                }
            }
            $bapi = new WPBlipAPI ();
        }
        else {
            $bapi = new BlipApi ();
        }

		$bapi->connect ();
		$bapi->uagent = 'WP Blip!/0.5.1 (http://wp-blip.googlecode.com)';
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

    if ($time_diff > (86400 * 365)) {
        return wp_blip_date_absolute ($ts, $options);
    }

    $ret = array ();
    if ($time_diff > (86400 * 30)) { ## miesiące
        $data = floor ($time_diff / 86400 / 30);
        $ret[] = _wp_blip_date_names ($data, 'm');
        $time_diff -= $data * 86400 * 30;
    }
    if ($time_diff > (86400 * 7)) { ## tygodnie
        $data = floor ($time_diff / 86400 / 7);
        $ret[] = _wp_blip_date_names ($data, 'w');
        $time_diff -= $data * 86400 * 7;
    }
    if ($time_diff > 86400) { ## dni
        $data = floor ($time_diff / 86400);
        $ret[] = _wp_blip_date_names ($data, 'd');
        $time_diff -= $data * 86400;
    }
    if ($time_diff > (3600)) { ## godziny
        $data = floor ($time_diff / 3600);
        $ret[] = _wp_blip_date_names ($data, 'H');
        $time_diff -= $data * 3600;
    }
    if ($time_diff > 60) { ## minuty
        $data = floor ($time_diff / 60);
        $ret[] = _wp_blip_date_names ($data, 'M');
        $time_diff -= $data * 60;
    }
    if ($time_diff > 0) { ## sekundy
        $ret[] = _wp_blip_date_names ($time_diff, 'S');
    }

    return join (', ', $ret);
}

function wp_blip_date_relative_simple ($ts, $options) {
    $time_diff = time () - $ts;
    if ($time_diff/31536000 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/31536000), 'y');
    }
    elseif ($time_diff/2592000 >= 1) {
        return _wp_blip_date_names (floor ($time_diff/2592000), 'm');
    }
    elseif ($time_diff/604800 >= 1) {
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
        return $units[$unit][0];
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

	return $GLOBALS['wp_blip_cacheroot'] . '/' . $options['login'] . '_' . $options['quant'] . '.cache.txt';
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

            $bapi       = wp_blip_connect ();
            $link_data  = $bapi->shortlink_read ($link[1]);
            if ($link_data['status_code'] == 200) {
                $link_href = $link_data['body']->original_link;
            }
        }

        return '<a href="'.$link_href.'" title="'.$match[0].'">'.$link_href.'</a>';
    }
    else if (substr ($match[1], 0, 4) == 'blip') {
        $link       = explode ('/', $match[1]);
        $title      = '';

        if ($opts['expand_linked_statuses'] && $link[1] == 's') {
            $bapi       = wp_blip_connect ();
            $st_data    = $bapi->status_read ($link[2]);
            if ($st_data['status_code'] == 200) {
                $title = explode ('/', $st_data['body']->user_path);
                $title = htmlspecialchars ('^'. $title[2] .': '. $st_data['body']->body);
            }
        }

        return '<a href="'.$match[0].'" title="'. $title .'">'.$match[0].'</a>';
    }

    return $match[0];
}

