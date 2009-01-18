<?php
/*
 * Plugin Name: WP-Blip!
 * Plugin URI: http://wp-blip.googlecode.com
 * Description: Wtyczka wyświetla ostatnie wpisy z <a href="http://blip.pl">blip.pl</a> (<a href="http://h2o.sztolcman.eu/wp-admin/options-general.php?page=WP-Blip">skonfiguruj</a>).
 * Version: 0.4
 * Author: Marcin 'MySZ' Sztolcman
 * Author URI: http://urzenia.net/
 * SVNVersion: $Id$
 */

define ('WP_BLIP_DEBUG', false);
$wp_blip_cacheroot = dirname (__FILE__);

$wp_blip_plchars    =   "\xc4\x85\xc4\x84\xc4\x86\xc4\x87\xc4\x98\xc4\x99\xc5\x81\xc5\x82\xc5\x83".
                        "\xc5\x84\xc5\x9a\xc5\x9b\xc5\xbb\xc5\xbc\xc5\xb9\xc5\xba\xc3\xb3\xc3\x93";
function wp_blip_debug () {
    $args = func_get_args ();
    echo '<pre>';
    foreach ($args as $arg) {
        print_r ($arg);
        echo "\n";
    }
    echo '</pre>';
}

if (
    (!is_dir ($wp_blip_cacheroot) || !is_writeable ($wp_blip_cacheroot)) &&
    function_exists ('sys_get_temp_dir')
) {
    $wp_blip_cacheroot = sys_get_temp_dir ();
}

function wp_blip_find_tags ($status) {
    $status = str_replace (array ('-', '_'), '', $status);
    preg_match_all ("!#([a-zA-Z0-9$wp_blip_plchars]+)!", $status, $statuses);
    return $statuses[1];
}

function wp_blip ($join="\n", $echo=0) {
	$login		= get_option ('wp_blip_login');
	$password	= get_option ('wp_blip_password');
	if (!$login || !$password) {
		return false;
	}

	$quant		= get_option ('wp_blip_quant');
	if (!$quant) {
		$quant	= 10;
	}

	$tpl		= get_option ('wp_blip_tpl');
	if (!$tpl) {
		$tpl	= '<li>(%date) %body</li>';
	}

	$time		= get_option ('wp_blip_time');
	if (!$time) {
		$time	= 300;
	}

	$tags		= get_option ('wp_blip_tags');
	if (!$tags) {
		$tags	= '';
	}

	$dateformat	= get_option ('wp_blip_dateformat');
	if (!$dateformat) {
		$dateformat	= '%Y-%m-%d %H:%M:%S';
	}



	$updates = wp_blip_cache ($login, $password, $quant, $time, $tags);

	$pat = array ('%date', '%body', '%url');
	$ret = array ();
	foreach ($updates as $update) {
		$rep = array (
			strftime ($dateformat, $update['created_at']),
			$update['body'],
			'http://blip.pl/s/'. $update['id'],
		);

		$ret[] = str_replace ($pat, $rep, $tpl);
	}

	if ($join === false) {
		return $ret;
	}

	$ret = join ($join, $ret);
	if ($echo) {
		echo $ret;
	}
	return $ret;
}

function wp_blip_start ($login, $password, $quant) {
	if (!get_option ('wp_blip_login')) {
		update_option ('wp_blip_login', $login);
	}
	if (!get_option ('wp_blip_password')) {
		update_option ('wp_blip_password', $password);
	}
	if (!get_option ('wp_blip_quant')) {
		update_option ('wp_blip_quant', $quant, 'Quant of updates to get from Blip!', 'no');
	}
}

function wp_blip_linkify ($status, $opts = array ()) {
    if (!$status) {
        return $status;
    }

    if (!isset ($opts['wo_users']) || !$opts['wo_users']) {
        $status = preg_replace ('#\^([-\w'.$wp_blip_plchars.']+)#', '<a href="http://$1.blip.pl/">^$1</a>', $status);
    }

    if (!isset ($opts['wo_tags']) || !$opts['wo_tags']) {
        $status = preg_replace ('/#([-\w'.$wp_blip_plchars.']+)/', '<a href="http://blip.pl/tags/$1">#$1</a>', $status);
    }

    if (!isset ($opts['wo_links']) || !$opts['wo_links']) {
        $status = preg_replace ('#(http://rdir\.pl/[a-zA-Z0-9]+)#', '<a href="$1">$1</a>', $status);
    }

    return $status;
}

function wp_blip_filter_statuses_by_tags ($tags, $statuses) {
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

function wp_blip_cache ($login, $password, $quant=null, $time=null, $tags=null) {
	if (!$login || !$password) {
		return false;
	}
	if (is_null ($quant) || $quant < 0) {
		$quant = 10;
	}
	if (is_null ($time) || $time < 0) {
		$time = 300;
	}
	if (is_null ($tags) || $tags < 0) {
		$tags = '';
	}

    if (!is_dir ($GLOBALS['wp_blip_cacheroot']) || !is_writeable ($GLOBALS['wp_blip_cacheroot'])) {
        trigger_error ('WP-Blip! cannot write cache file in '. $GLOBALS['wp_blip_cacheroot'] .' directory.', E_USER_NOTICE);
    }

	$cachefile = $GLOBALS['wp_blip_cacheroot'] . '/' . $login . '_' . $quant . '.cache.txt';

	$ret = array ();
	if ((!defined ('WP_BLIP_DEBUG') || !WP_BLIP_DEBUG) && $time && file_exists ($cachefile) && (filemtime ($cachefile) + $time) > time ()) {
		return unserialize (stripslashes (file_get_contents ($cachefile)));
	}
	else {
		if (!$login || !$password) {
			return false;
		}
		if (!$quant) {
			$quant = 10;
			update_option ('wp_blip_quant', $quant);
		}

		require_once 'blipapi.php';
		$bapi = new BlipApi ($login, $password);
		$bapi->connect ();
		$bapi->uagent = 'WP Blip!/0.4 (http://wp-blip.googlecode.com)';

        ## pobieramy statusy
		$statuses = $bapi->status_read (null, null, array (), false, $quant);

        ## jeśli filtrujemy po tagach:
        if ($tags) {
            ## rozdzielamy tagi
            $tags = preg_split ('!\s!', $tags);

            ## odfiltrowujemy niechciane statusy
            $statuses = wp_blip_filter_statuses_by_tags ($tags, $statuses['body']);

            ## szukamy pozostałych statusów jeśli trzeba
            $offset = $quant;
            while (count ($statuses) < $quant) {
		        $filtered = $bapi->status_read (null, null, array (), false, 20, $offset);
                $filtered = wp_blip_filter_statuses_by_tags ($tags, $filtered['body']);
                if (count ($filtered)) {
                    $statuses = array_merge ($statuses, $filtered);
                }
                $offset += 20;
            }

            ## jak za dużo to ucinamy nadmiarowe statusy
            if (count ($statuses) > $quant) {
                $statuses = array_splice ($statuses, 0, $quant);
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


function wp_blip_admin_actions () {
    add_options_page('WP Blip!', 'WP Blip!', 7, 'WP-Blip', 'wp_blip_option_page');
}

function wp_blip_option_page () {
	include_once 'wp-blip-options.php';
}

add_action('admin_menu', 'wp_blip_admin_actions');

?>
