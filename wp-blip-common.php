<?php
/*
 * Common functions file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

$wp_blip_cacheroot = dirname (__FILE__);
if (
    (!is_dir ($wp_blip_cacheroot) || !is_writeable ($wp_blip_cacheroot)) &&
    function_exists ('sys_get_temp_dir')
) {
    $wp_blip_cacheroot = sys_get_temp_dir ();
}

$wp_blip_plchars    =   "\xc4\x85\xc4\x84\xc4\x86\xc4\x87\xc4\x98\xc4\x99\xc5\x81\xc5\x82\xc5\x83".
                        "\xc5\x84\xc5\x9a\xc5\x9b\xc5\xbb\xc5\xbc\xc5\xb9\xc5\xba\xc3\xb3\xc3\x93";

function wp_blip_debug () {
    $args = func_get_args ();
    echo '<pre>';
    foreach ($args as $arg) {
        echo htmlspecialchars (print_r ($arg, 1));
        echo "\n";
    }
    echo '</pre>';
}

function wp_blip ($join="\n", $echo=0) {
    $options = wp_blip_get_options ();
	if (!$options['login']) {
		return false;
	}

	$updates = wp_blip_cache ();

	$pat = array ('%date', '%body', '%url');
	$ret = array ();
	foreach ($updates as $update) {
		$rep = array (
			strftime ($options['dateformat'], $update['created_at']),
			$update['body'],
			'http://blip.pl/s/'. $update['id'],
		);

		$ret[] = str_replace ($pat, $rep, $options['tpl']);
	}

	if ($join === false) {
		return $ret;
	}

	$ret = join ($join, $ret);
    if ($options['tpl_container_pre'] !== false) {
        $ret = $options['tpl_container_pre'] . $ret;
    }
    if ($options['tpl_container_post'] !== false) {
        $ret .= $options['tpl_container_post'];
    }
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

	$cachefile = wp_blip_get_cache_filename ();

	$ret = array ();
	if ((!defined ('WP_BLIP_DEBUG') || !WP_BLIP_DEBUG) && $options['time'] && file_exists ($cachefile) && (filemtime ($cachefile) + $options['time']) > time ()) {
		return unserialize (stripslashes (file_get_contents ($cachefile)));
	}
	else {
		if (!$options['quant']) {
			$options['quant'] = 10;
			update_option ('wp_blip_quant', $options['quant']);
		}

		require_once 'blipapi.php';
		$bapi = new BlipApi ();
		$bapi->connect ();
		$bapi->uagent = 'WP Blip!/0.4.7 (http://wp-blip.googlecode.com)';

        ## pobieramy statusy
		$statuses = $bapi->status_read (null, $options['login'], array (), false, $options['quant']);

        ## jeśli filtrujemy po tagach:
        if ($options['tags']) {
            ## rozdzielamy tagi
            $options['tags'] = preg_split ('!\s!', $options['tags']);

            ## odfiltrowujemy niechciane statusy
            $statuses = wp_blip_filter_statuses_by_tags ($options['tags'], $statuses['body']);

            ## szukamy pozostałych statusów jeśli trzeba
            $offset = $options['quant'];
            while (count ($statuses) < $options['quant']) {
		        $filtered = $bapi->status_read (null, $options['login'], array (), false, 20, $offset);
                $filtered = wp_blip_filter_statuses_by_tags ($options['tags'], $filtered['body']);
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

		unset ($bapi);

		$save = array ();
		foreach ($statuses as $status) {
            $date = preg_split ('#[: -]#', $status->created_at);
			$save[] = array (
				'created_at'	=> mktime ($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]),
				'body'			=> wp_blip_linkify ($status->body),
				'id'			=> $status->id,
			);
		}

		@file_put_contents ($cachefile, serialize ($save), LOCK_EX);
		return $save;
	}
}

function wp_blip_cache_invalidate () {
    $cachefile = wp_blip_get_cache_filename ();

    if (is_file ($cachefile)) {
        @unlink ($cachefile);
    }
    return !is_file ($cachefile);
}

function wp_blip_filter_statuses_by_tags ($tags, $statuses) {
    $filtered = array ();
    foreach ($statuses as $status) {
        foreach (wp_blip_find_tags ($status->body) as $tag) {
            if (in_array ($tag, $tags)) {
                $filtered[] = $status;
                continue 2;
            }
        }
    }

    return $filtered;
}

function wp_blip_find_tags ($status) {
    $status = str_replace (array ('-', '_'), '', $status);
    preg_match_all ("!#([a-zA-Z0-9$wp_blip_plchars]+)!", $status, $statuses);
    return $statuses[1];
}

function wp_blip_get_cache_filename () {
    $options = wp_blip_get_options ();

	return $GLOBALS['wp_blip_cacheroot'] . '/' . $options['login'] . '_' . $options['quant'] . '.cache.txt';
}

function wp_blip_get_options () {
    static $options = null;

    if (is_null ($options)) {
        $options = array (
            'login'                 => '',
            'quant'                 => 10,
            'tpl'                   => '<li>(%date) %body</li>',
            'time'                  => 300,
            'tags'                  => '',
            'dateformat'            => '%Y-%m-%d %H:%M:%S',
            'tpl_container_pre'     => '<ul>',
            'tpl_container_post'    => '</ul>',
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

    return $options;
}

function wp_blip_linkify ($status, $opts = array ()) {
    if (!$status || gettype ($status) != 'string') {
        return $status;
    }

    if (!isset ($opts['wo_blip']) || !$opts['wo_blip']) {
        $status = preg_replace ('#(https?://(?:www\.)?blip\.pl/[/a-zA-Z0-9]+)#', '<a href="$1">$1</a>', $status);
    }

    if (!isset ($opts['wo_users']) || !$opts['wo_users']) {
        $status = preg_replace ('#\^([-\w'.$wp_blip_plchars.']+)#', '<a href="http://$1.blip.pl/">^$1</a>', $status);
    }

    if (!isset ($opts['wo_tags']) || !$opts['wo_tags']) {
        $status = preg_replace ('/#([-\w'.$wp_blip_plchars.']+)/', '<a href="http://blip.pl/tags/$1">#$1</a>', $status);
    }

    if (!isset ($opts['wo_links']) || !$opts['wo_links']) {
        $status = preg_replace ('#(https?://rdir\.pl/[a-zA-Z0-9]+)#', '<a href="$1">$1</a>', $status);
    }

    return $status;
}

