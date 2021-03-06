<?php
/**
 * Extension to self monitor Pandora FMS Console
 *
 * @category   Main page
 * @package    Pandora FMS
 * @subpackage Introduction
 * @version    1.0.0
 * @license    See below
 *
 *    ______                 ___                    _______ _______ ________
 *   |   __ \.-----.--.--.--|  |.-----.----.-----. |    ___|   |   |     __|
 *  |    __/|  _  |     |  _  ||  _  |   _|  _  | |    ___|       |__     |
 * |___|   |___._|__|__|_____||_____|__| |___._| |___|   |__|_|__|_______|
 *
 * ============================================================================
 * Copyright (c) 2005-2019 Artica Soluciones Tecnologicas
 * Please see http://pandorafms.org for full contribution list
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation for version 2.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ============================================================================
 */

 // Config functions.
require_once 'include/config.php';

// This solves problems in enterprise load.
global $config;

check_login();

require_once 'include/functions_reporting.php';
require_once 'include/functions_tactical.php';
require_once $config['homedir'].'/include/functions_graph.php';

if (tags_has_user_acl_tags()) {
    ui_print_tags_warning();
}

$user_strict = (bool) db_get_value(
    'strict_acl',
    'tusuario',
    'id_user',
    $config['id_user']
);
$all_data = tactical_status_modules_agents(
    $config['id_user'],
    $user_strict,
    'AR',
    $user_strict
);
$data = [];

$data['monitor_not_init'] = (int) $all_data['_monitors_not_init_'];
$data['monitor_unknown'] = (int) $all_data['_monitors_unknown_'];
$data['monitor_ok'] = (int) $all_data['_monitors_ok_'];
$data['monitor_warning'] = (int) $all_data['_monitors_warning_'];
$data['monitor_critical'] = (int) $all_data['_monitors_critical_'];
$data['monitor_not_normal'] = (int) $all_data['_monitor_not_normal_'];
$data['monitor_alerts'] = (int) $all_data['_monitors_alerts_'];
$data['monitor_alerts_fired'] = (int) $all_data['_monitors_alerts_fired_'];

$data['total_agents'] = (int) $all_data['_total_agents_'];

$data['monitor_checks'] = (int) $all_data['_monitor_checks_'];
if (!empty($all_data)) {
    if ($data['monitor_not_normal'] > 0 && $data['monitor_checks'] > 0) {
        $data['monitor_health'] = format_numeric((100 - ($data['monitor_not_normal'] / ($data['monitor_checks'] / 100))), 1);
    } else {
        $data['monitor_health'] = 100;
    }

    if ($data['monitor_not_init'] > 0 && $data['monitor_checks'] > 0) {
        $data['module_sanity'] = format_numeric((100 - ($data['monitor_not_init'] / ($data['monitor_checks'] / 100))), 1);
    } else {
        $data['module_sanity'] = 100;
    }

    if (isset($data['alerts'])) {
        if ($data['monitor_alerts_fired'] > 0 && $data['alerts'] > 0) {
            $data['alert_level'] = format_numeric((100 - ($data['monitor_alerts_fired'] / ($data['alerts'] / 100))), 1);
        } else {
            $data['alert_level'] = 100;
        }
    } else {
        $data['alert_level'] = 100;
        $data['alerts'] = 0;
    }

    $data['monitor_bad'] = ($data['monitor_critical'] + $data['monitor_warning']);

    if ($data['monitor_bad'] > 0 && $data['monitor_checks'] > 0) {
        $data['global_health'] = format_numeric((100 - ($data['monitor_bad'] / ($data['monitor_checks'] / 100))), 1);
    } else {
        $data['global_health'] = 100;
    }

    $data['server_sanity'] = format_numeric((100 - $data['module_sanity']), 1);
}


?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        
        <td width="25%" style="padding-right: 20px;" valign="top">
            
            
            <?php
            //
            // Overview Table.
            //
            $table = new stdClass();
            $table->class = 'databox';
            $table->cellpadding = 4;
            $table->cellspacing = 4;
            $table->head = [];
            $table->data = [];
            $table->headstyle[0] = 'text-align:center;';
            $table->width = '100%';
            $table->head[0] = '<span>'.__('%s Overview', get_product_name()).'</span>';
            $table->head_colspan[0] = 4;

            // Indicators.
            $tdata = [];
            $stats = reporting_get_stats_indicators($data, 120, 10, false);
            $status = '<table class="status_tactical">';
            foreach ($stats as $stat) {
                $status .= '<tr><td><b>'.$stat['title'].'</b></td><td>'.$stat['graph'].'</td></tr>';
            }

            $status .= '</table>';
            $table->data[0][0] = $status;
            $table->rowclass[] = '';

            $table->data[] = $tdata;

            // Alerts.
            $tdata = [];
            $tdata[0] = reporting_get_stats_alerts($data);
            $table->rowclass[] = '';
            $table->data[] = $tdata;

            // Modules by status.
            $tdata = [];
            $tdata[0] = reporting_get_stats_modules_status($data, 180, 100);
            $table->rowclass[] = '';
            $table->data[] = $tdata;

            // Total agents and modules.
            $tdata = [];
            $tdata[0] = reporting_get_stats_agents_monitors($data);
            $table->rowclass[] = '';
            $table->data[] = $tdata;

            // Users.
            if (users_is_admin()) {
                $tdata = [];
                $tdata[0] = reporting_get_stats_users($data);
                $table->rowclass[] = '';
                $table->data[] = $tdata;
            }

            html_print_table($table);
            unset($table);
            ?>
            
            
        </td>
        
        <td width="75%" valign="top">
            
            
            <?php
            $options = [];
            $options['id_user'] = $config['id_user'];
            $options['modal'] = false;
            $options['limit'] = 3;
            $news = get_news($options);


            if (!empty($news)) {
                // NEWS BOARD.
                echo '<div id="news_board">';

                echo '<table cellpadding="0" width=100% cellspacing="0" class="databox filters">';
                echo '<tr><th style="text-align:center;"><span >'.__('News board').'</span></th></tr>';
                if ($config['prominent_time'] == 'timestamp') {
                    $comparation_suffix = '';
                } else {
                    $comparation_suffix = __('ago');
                }

                foreach ($news as $article) {
                    $text_bbdd = io_safe_output($article['text']);
                    $text = html_entity_decode($text_bbdd);
                    echo '<tr><th class="green_title">'.$article['subject'].'</th></tr>';
                    echo '<tr><td>'.__('by').' <b>'.$article['author'].'</b> <i>'.ui_print_timestamp($article['timestamp'], true).'</i> '.$comparation_suffix.'</td></tr>';
                    echo '<tr><td class="datos">';
                    if ($article['id_news'] == 1) {
                        echo '<center><img src="./images/welcome_image.png" alt="img colabora con nosotros - Support" width="191" height="207"></center>';
                    }

                    echo nl2br($text);
                    echo '</td></tr>';
                }

                echo '</table>';
                echo '</div>';
                // News board.
                echo '<br><br>';

                // END OF NEWS BOARD.
            }

            // LAST ACTIVITY.
            // Show last activity from this user.
            echo '<div id="activity">';

            $table = new stdClass();
            $table->class = 'info_table';
            $table->cellpadding = 0;
            $table->cellspacing = 0;
            $table->width = '100%';
            // Don't specify px.
            $table->data = [];
            $table->size = [];
            $table->size[0] = '5%';
            $table->size[1] = '15%';
            $table->size[2] = '15%';
            $table->size[3] = '10%';
            $table->size[4] = '25%';
            $table->head = [];
            $table->head[0] = __('User');
            $table->head[1] = __('Action');
            $table->head[2] = __('Date');
            $table->head[3] = __('Source IP');
            $table->head[4] = __('Comments');
            $table->title = '<span>'.__('This is your last activity performed on the %s console', get_product_name()).'</span>';
            $sql = sprintf(
                'SELECT id_usuario,accion, ip_origen,descripcion,utimestamp
						FROM tsesion
						WHERE (`utimestamp` > UNIX_TIMESTAMP(NOW()) - '.SECONDS_1WEEK.") 
							AND `id_usuario` = '%s' ORDER BY `utimestamp` DESC LIMIT 10",
                $config['id_user']
            );


            $sessions = db_get_all_rows_sql($sql);

            if ($sessions === false) {
                $sessions = [];
            }

            foreach ($sessions as $session) {
                $data = [];
                $session_id_usuario = $session['id_usuario'];
                $session_ip_origen = $session['ip_origen'];



                $data[0] = '<strong>'.$session_id_usuario.'</strong>';
                $data[1] = ui_print_session_action_icon($session['accion'], true).' '.$session['accion'];
                $data[2] = ui_print_help_tip(
                    date($config['date_format'], $session['utimestamp']),
                    true
                ).human_time_comparation($session['utimestamp'], 'tiny');
                $data[3] = $session_ip_origen;
                $description = str_replace([',', ', '], ', ', $session['descripcion']);
                if (strlen($description) > 100) {
                    $data[4] = '<div >'.io_safe_output(substr($description, 0, 150).'...').'</div>';
                } else {
                    $data[4] = '<div >'.io_safe_output($description).'</div>';
                }

                array_push($table->data, $data);
            }

            echo "<div style='width:100%; overflow-x:auto;'>";
            html_print_table($table);
            unset($table);
            echo '</div>';
            echo '</div>';
            // END OF LAST ACTIVIYY.
            ?>
            
            
        </td>
        
    </tr>
</table>
