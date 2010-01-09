<?php
/*
 * Interface file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

if (!defined ('WP_BLIP')) exit;
if (!current_user_can ('manage_options') ) {
    wp_die (__ ('You do not have sufficient permissions to manage options for this blog.'));
}

require_once 'wp-blip-common.php';

$wp_blip_options = wp_blip_get_options ();

?>
<script type="text/javascript">
function wp_blip_dateformat () {
    jQuery ('#wp_blip_dateformat').attr ('readonly', function () {
        if (jQuery ('#wp_blip_datetype_absolute').attr ('checked')) {
            jQuery ('#wp_blip_dateformat').css ('backgroundColor', 'transparent');
        }
        else {
            jQuery ('#wp_blip_dateformat').css ('backgroundColor', '#ccc');
            return 'readonly';
        }
    });
}
function wp_blip_absolute_from () {
    jQuery ('#wp_blip_absolute_from').attr ('readonly', function () {
        if (jQuery ('#wp_blip_datetype_absolute').attr ('checked')) {
            jQuery ('#wp_blip_absolute_from').css ('backgroundColor', '#ccc');
            return 'readonly';
        }
        else {
            jQuery ('#wp_blip_absolute_from').css ('backgroundColor', 'transparent');
        }
    });
}
function init () {
    wp_blip_dateformat ();
    wp_blip_absolute_from ();

    jQuery ('#wp_blip_datetype_relative').change (function () {
        wp_blip_dateformat ();
        wp_blip_absolute_from ();
    });
    jQuery ('#wp_blip_datetype_relative_simple').change (function () {
        wp_blip_dateformat ();
        wp_blip_absolute_from ();
    });
    jQuery ('#wp_blip_datetype_absolute').change (function () {
        wp_blip_dateformat ();
        wp_blip_absolute_from ();
    });
}

jQuery (init);
</script>
<style type="text/css">
div.wp_blip dt {
    font-style: italic;
}
div.wp_blip dd {
    padding-left: 1em;
}
</style>
<div class="wrap wp_blip">
    <h2>WP-Blip!</h2>
    <form method="post" action="options.php">
        <?php wp_nonce_field('update-options') ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wp_blip_login">Użytkownik w serwisie <a href="http://blip.pl">Blip!</a>:</label></th>
                <td><input type="text" name="wp_blip_login" id="wp_blip_login" value="<?php echo htmlentities2 ($wp_blip_options['login']) ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_quant">Ilość statusów do pobrania:</label></th>
                <td><input type="text" name="wp_blip_quant" id="wp_blip_quant" value="<?php echo htmlentities2 ($wp_blip_options['quant']) ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_time">Okres trwałości pamięci podręcznej:</label></th>
                <td><input type="text" name="wp_blip_time" id="wp_blip_time" value="<?php echo htmlentities2 ($wp_blip_options['time']) ?>" /><br />
                    W sekundach</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_tags">Śledź tylko wymienione tagi:</label></th>
                <td><input type="text" name="wp_blip_tags" id="wp_blip_tags" value="<?php echo htmlentities2 ($wp_blip_options['tags']) ?>" /><br />
                    rozdzielaj poszczególne tagi znakiem spacji lub zostaw puste jeśli nie chcesz filtrować statusów</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_tpl_container_pre">Przed listą statusów wstaw:</label></th>
                <td><input type="text" name="wp_blip_tpl_container_pre" id="wp_blip_tpl_container_pre" value="<?php echo htmlentities2 ($wp_blip_options['tpl_container_pre']) ?>" size="50"/><br />
                    Przykład: &lt;ul class=&quot;blip_log&quot;&gt;
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_tpl_container_post">Po liście statusów wstaw:</label></th>
                <td><input type="text" name="wp_blip_tpl_container_post" id="wp_blip_tpl_container_post" value="<?php echo htmlentities2 ($wp_blip_options['tpl_container_post']) ?>" size="50"/><br />
                    Przykład: &lt;/ul&gt;
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_tpl">Szablon wiadomości:</label></th>
                <td><input type="text" name="wp_blip_tpl" id="wp_blip_tpl" value="<?php echo htmlentities2 ($wp_blip_options['tpl']) ?>" size="50"/><br />
                    %url - zostanie zastąpione permalinkiem do statusu<br />
                    %body - treść statusu<br />
                    %date - data ustawienia statusu<br />
                    %picture - jeśli status zawiera obrazek, zostanie w t miejsce wstawiona wypełniona zawartość szablonu obrazka<br />
                    Przykład: &lt;li&gt;&lt;h4&gt;&lt;a href=&quot;%url&quot;&gt;%date&lt;/a&gt;&lt;/h4&gt;&lt;br /&gt;%body&lt;/li&gt;
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_picture_tpl">Szablon obrazka:</label></th>
                <td><input type="text" name="wp_blip_picture_tpl" id="wp_blip_picture_tpl" value="<?php echo htmlentities2 ($wp_blip_options['picture_tpl']) ?>" size="50"/><br />
                    %src - zostanie zastąpione permalinkiem do obrazka<br />
                    Przykłady:<br />
                        &lt;a href="%src" class="thickbox"&gt;&lt;img src="%src" width="100px" /&gt;&lt;/a&gt;<br />
                        &lt;a href="%src" class="thickbox" rel="blip"&gt;&lt;img src="%src" width="100px" /&gt;&lt;/a&gt;
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Sposób wyświetlania daty statusu:</th>
                <td>
                    <label for="wp_blip_datetype_relative"><input
                        type="radio" name="wp_blip_datetype" value="relative" id="wp_blip_datetype_relative"
                        <?php echo $wp_blip_options['datetype'] == 'relative' ? 'checked="checked"' : ''; ?>
                        /> relatywny</label>
                    <label for="wp_blip_datetype_relative_simple"><input
                        type="radio" name="wp_blip_datetype" value="relative_simple" id="wp_blip_datetype_relative_simple"
                        <?php echo $wp_blip_options['datetype'] == 'relative_simple' ? 'checked="checked"' : ''; ?>
                        /> relatywny (uproszczony)</label>
                    <label for="wp_blip_datetype_absolute"><input
                        type="radio" name="wp_blip_datetype" value="absolute" id="wp_blip_datetype_absolute"
                        <?php echo $wp_blip_options['datetype'] == 'absolute' ? 'checked="checked"' : ''; ?>
                        /> absolutny</label><br />

                    Przykłady:
                    <dl>
                        <dt>relatywny:</dt>
                        <dd>2 minuty</dd>
                        <dd>3 godziny, 2 minuty</dd>
                        <dd>4 dni, 7 godzin, 4 minuty</dd>
                        <dt>relatywny uproszczony:</dt>
                        <dd>2 minuty</dd>
                        <dd>3 godziny</dd>
                        <dd>4 dni</dd>
                        <dt>absolutny:</dt>
                        <dd>20.12.2009 20:01:37</dd>
                    </dl>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_absolute_from">Absolutne od:</label></th>
                <td><input type="text" name="wp_blip_absolute_from" id="wp_blip_absolute_from"
                    value="<?php echo htmlentities2 ($wp_blip_options['absolute_from']) ?>"
                    size="50" /><br />
                    Wartość w dniach, po przekroczeniu której data relatywna będzie wyświetlona jako absolutna
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_dateformat">Szablon daty:</label></th>
                <td><input type="text" name="wp_blip_dateformat" id="wp_blip_dateformat"
                    value="<?php echo htmlentities2 ($wp_blip_options['dateformat']) ?>"
                    size="50" /><br />
                    Szczegóły: <a href="http://php.net/strftime">php.net/strftime</a> (domyślnie: %Y-%m-%d %H:%M:%S)
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Rozwiń linki <code>rdir.pl</code>:</th>
                <td>
                    <label for="wp_blip_expand_rdir_yes"><input
                        type="radio" name="wp_blip_expand_rdir" id="wp_blip_expand_rdir_yes" value="1"
                        <?php echo $wp_blip_options['expand_rdir'] ? 'checked="checked"' : ''; ?>
                        /> rozwiń</label>
                    <label for="wp_blip_expand_rdir_no"><input
                        type="radio" name="wp_blip_expand_rdir" id="wp_blip_expand_rdir_no" value="0"
                        <?php echo $wp_blip_options['expand_rdir'] ? '' : 'checked="checked"'; ?>
                        /> nie rozwijaj</label><br />
                    Rozwijanie linków nieco spowalnia pobieranie listy statusów.
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Wczytaj linkowane statusy:</th>
                <td>
                    <label for="wp_blip_expand_linked_statuses_yes"><input
                        type="radio" name="wp_blip_expand_linked_statuses" id="wp_blip_expand_linked_statuses_yes" value="1"
                        <?php echo $wp_blip_options['expand_linked_statuses'] ? 'checked="checked"' : ''; ?>
                        /> wczytaj</label>
                    <label for="wp_blip_expand_linked_statuses_no"><input
                        type="radio" name="wp_blip_expand_linked_statuses" id="wp_blip_expand_linked_statuses_no" value="0"
                        <?php echo $wp_blip_options['expand_linked_statuses'] ? '' : 'checked="checked"'; ?>
                        /> nie wczytuj</label><br />
                    Wczytywanie linkowanych statusów nieco spowalnia pobieranie listy statusów.
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wp_blip_onerror_email">W razie błędu wyślij powiadomienie na adres:</label></th>
                <td>
                    <input type="text" name="wp_blip_onerror_email" id="wp_blip_onerror_email" value="<?php echo htmlentities2 ($wp_blip_options['onerror_email']) ?>" /><br />
                    Zostaw puste jeśli nie chcesz otrzymywać powiadomień o błędach.
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Wyczyść cache:</th>
                <td><a href="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-blip/wp-blip-ajax.php?ajax=1&amp;action=cache_invalidate"
                    onclick="jQuery.get (this.href, {}, function (d, s) {alert (d);}); return false">wyczyść</a></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="wp_blip_login,wp_blip_quant,wp_blip_time,wp_blip_tpl,wp_blip_dateformat,wp_blip_tags,wp_blip_tpl_container_pre,wp_blip_tpl_container_post,wp_blip_expand_rdir,wp_blip_expand_linked_statuses,wp_blip_datetype,wp_blip_onerror_email,wp_blip_absolute_from,wp_blip_picture_tpl" />
        </p>
    </form>
</div>

