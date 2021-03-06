<?php
// Copyright (c) 2011-2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// Turn on output buffering.
// The entire buffer will be discarded later so that any accidental output
// does not corrupt images generated by fgraph.
ob_start();

global $config;

if (empty($config['homedir'])) {
    include_once '../../include/config.php';
    global $config;
}

require_once $config['homedir'].'/include/functions.php';

$ttl = get_parameter('ttl', 1);
$graph_type = get_parameter('graph_type', '');

if (!empty($graph_type)) {
    include_once $config['homedir'].'/include/functions_html.php';
    include_once $config['homedir'].'/include/graphs/functions_gd.php';
    include_once $config['homedir'].'/include/graphs/functions_utils.php';
    include_once $config['homedir'].'/include/graphs/functions_d3.php';
    include_once $config['homedir'].'/include/graphs/functions_flot.php';
}

// Clean the output buffer and turn off output buffering
ob_end_clean();

switch ($graph_type) {
    case 'histogram':
        $width = get_parameter('width');
        $height = get_parameter('height');
        $data = json_decode(io_safe_output(get_parameter('data')), true);

        $max = get_parameter('max');
        $title = get_parameter('title');
        $mode = get_parameter('mode', 1);
        gd_histogram($width, $height, $mode, $data, $max, $config['fontpath'], $title);
    break;

    case 'progressbar':
        $width = get_parameter('width');
        $height = get_parameter('height');
        $progress = get_parameter('progress');

        $out_of_lim_str = io_safe_output(get_parameter('out_of_lim_str', false));
        $out_of_lim_image = get_parameter('out_of_lim_image', false);

        $title = get_parameter('title');

        $mode = get_parameter('mode', 1);

        $fontsize = get_parameter('fontsize', 10);

        $value_text = get_parameter('value_text', '');
        $colorRGB = get_parameter('colorRGB', '');

        gd_progress_bar(
            $width,
            $height,
            $progress,
            $title,
            $config['fontpath'],
            $out_of_lim_str,
            $out_of_lim_image,
            $mode,
            $fontsize,
            $value_text,
            $colorRGB
        );
    break;

    case 'progressbubble':
        $width = get_parameter('width');
        $height = get_parameter('height');
        $progress = get_parameter('progress');

        $out_of_lim_str = io_safe_output(get_parameter('out_of_lim_str', false));
        $out_of_lim_image = get_parameter('out_of_lim_image', false);

        $title = get_parameter('title');

        $mode = get_parameter('mode', 1);

        $fontsize = get_parameter('fontsize', 7);

        $value_text = get_parameter('value_text', '');
        $colorRGB = get_parameter('colorRGB', '');

        gd_progress_bubble(
            $width,
            $height,
            $progress,
            $title,
            $config['fontpath'],
            $out_of_lim_str,
            $out_of_lim_image,
            $mode,
            $fontsize,
            $value_text,
            $colorRGB
        );
    break;
}


function progressbar(
    $progress,
    $width,
    $height,
    $title,
    $font,
    $mode=1,
    $out_of_lim_str=false,
    $out_of_lim_image=false,
    $ttl=1
) {
    $graph = [];

    $graph['progress'] = $progress;
    $graph['width'] = $width;
    $graph['height'] = $height;
    $graph['out_of_lim_str'] = $out_of_lim_str;
    $graph['out_of_lim_image'] = $out_of_lim_image;
    $graph['title'] = $title;
    $graph['font'] = $font;
    $graph['mode'] = $mode;

    $id_graph = serialize_in_temp($graph, null, $ttl);
    if (is_metaconsole()) {
        return "<img src='../../include/graphs/functions_gd.php?static_graph=1&graph_type=progressbar&ttl=".$ttl.'&id_graph='.$id_graph."'>";
    } else {
        return "<img src='include/graphs/functions_gd.php?static_graph=1&graph_type=progressbar&ttl=".$ttl.'&id_graph='.$id_graph."'>";
    }
}


function vbar_graph(
    $chart_data,
    $width,
    $height,
    $color,
    $legend,
    $long_index,
    $no_data_image,
    $xaxisname='',
    $yaxisname='',
    $water_mark='',
    $font='',
    $font_size='',
    $unit='',
    $ttl=1,
    $homeurl='',
    $backgroundColor='white',
    $from_ux=false,
    $from_wux=false,
    $tick_color='white'
) {
    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    if (empty($chart_data)) {
        return html_print_image(
            $no_data_image,
            true,
            [
                'width'  => $width,
                'height' => $height,
                'title'  => __('No data to show'),
            ],
            false,
            true
        );
    }

    if ($ttl == 2) {
        $params = [
            'chart_data'      => $chart_data,
            'width'           => $width,
            'height'          => $height,
            'color'           => $color,
            'legend'          => $legend,
            'long_index'      => $long_index,
            'homeurl'         => $homeurl,
            'unit'            => $unit,
            'water_mark_url'  => $water_mark_url,
            'homedir'         => $homedir,
            'font'            => $font,
            'font_size'       => $font_size,
            'from_ux'         => $from_ux,
            'from_wux'        => $from_wux,
            'backgroundColor' => $backgroundColor,
            'tick_color'      => $tick_color,
        ];
        return generator_chart_to_pdf('vbar', $params);
    }

    return flot_vcolumn_chart(
        $chart_data,
        $width,
        $height,
        $color,
        $legend,
        $long_index,
        $homeurl,
        $unit,
        $water_mark_url,
        $homedir,
        $font,
        $font_size,
        $from_ux,
        $from_wux,
        $backgroundColor,
        $tick_color
    );
}


function area_graph(
    $agent_module_id,
    $array_data,
    $legend,
    $series_type,
    $color,
    $date_array,
    $data_module_graph,
    $params,
    $water_mark,
    $array_events_alerts
) {
    global $config;

    include_once 'functions_flot.php';

    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    return flot_area_graph(
        $agent_module_id,
        $array_data,
        $legend,
        $series_type,
        $color,
        $date_array,
        $data_module_graph,
        $params,
        $water_mark,
        $array_events_alerts
    );
}


function stacked_bullet_chart(
    $chart_data,
    $width,
    $height,
    $color,
    $legend,
    $long_index,
    $no_data_image,
    $xaxisname='',
    $yaxisname='',
    $water_mark='',
    $font='',
    $font_size='',
    $unit='',
    $ttl=1,
    $homeurl='',
    $backgroundColor='white'
) {
    include_once 'functions_d3.php';

    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    if (empty($chart_data)) {
        return '<img src="'.$no_data_image.'" />';
    }

    return d3_bullet_chart(
        $chart_data,
        $width,
        $height,
        $color,
        $legend,
        $homeurl,
        $unit,
        $font,
        $font_size
    );

}


function stacked_gauge(
    $chart_data,
    $width,
    $height,
    $color,
    $legend,
    $no_data_image,
    $font='',
    $font_size='',
    $unit='',
    $homeurl=''
) {
    include_once 'functions_d3.php';

    if (empty($chart_data)) {
        return '<img src="'.$no_data_image.'" />';
    }

    return d3_gauges(
        $chart_data,
        $width,
        $height,
        $color,
        $legend,
        $homeurl,
        $unit,
        $font,
        ($font_size + 2),
        $no_data_image
    );
}


function hbar_graph(
    $chart_data,
    $width,
    $height,
    $color,
    $legend,
    $long_index,
    $no_data_image,
    $xaxisname='',
    $yaxisname='',
    $water_mark='',
    $font='',
    $font_size='',
    $unit='',
    $ttl=1,
    $homeurl='',
    $backgroundColor='white',
    $tick_color='white',
    $val_min=null,
    $val_max=null
) {
    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    if (empty($chart_data)) {
        return html_print_image(
            $no_data_image,
            true,
            [
                'width'  => $width,
                'height' => $height,
                'title'  => __('No data to show'),
            ],
            false,
            true
        );
    }

    if ($ttl == 2) {
        $params = [
            'chart_data'      => $chart_data,
            'width'           => $width,
            'height'          => $height,
            'water_mark_url'  => $water_mark_url,
            'font'            => $font,
            'font_size'       => $font_size,
            'backgroundColor' => $backgroundColor,
            'tick_color'      => $tick_color,
            'val_min'         => $val_min,
            'val_max'         => $val_max,
        ];
        return generator_chart_to_pdf('hbar', $params);
    }

    return flot_hcolumn_chart(
        $chart_data,
        $width,
        $height,
        $water_mark_url,
        $font,
        $font_size,
        $backgroundColor,
        $tick_color,
        $val_min,
        $val_max
    );
}


function pie_graph(
    $chart_data,
    $width,
    $height,
    $others_str='other',
    $homedir='',
    $water_mark='',
    $font='',
    $font_size=8,
    $ttl=1,
    $legend_position=false,
    $colors='',
    $hide_labels=false
) {
    if (empty($chart_data)) {
        return graph_nodata_image($width, $height, 'pie');
    }

    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    // This library allows only 8 colors
    $max_values = 9;

    // Remove the html_entities
    $temp = [];
    foreach ($chart_data as $key => $value) {
        $temp[io_safe_output($key)] = $value;
    }

    $chart_data = $temp;

    if (count($chart_data) > $max_values) {
        $chart_data_trunc = [];
        $n = 1;
        foreach ($chart_data as $key => $value) {
            if ($n < $max_values) {
                $chart_data_trunc[$key] = $value;
            } else {
                if (!isset($chart_data_trunc[$others_str])) {
                    $chart_data_trunc[$others_str] = 0;
                }

                $chart_data_trunc[$others_str] += $value;
            }

            $n++;
        }

        $chart_data = $chart_data_trunc;
    }

    if ($ttl == 2) {
        $params = [
            'values'          => array_values($chart_data),
            'keys'            => array_keys($chart_data),
            'width'           => $width,
            'height'          => $height,
            'water_mark_url'  => $water_mark_url,
            'font'            => $font,
            'font_size'       => $font_size,
            'legend_position' => $legend_position,
            'colors'          => $colors,
            'hide_labels'     => $hide_labels,
        ];

        return generator_chart_to_pdf('pie_chart', $params);
    }

    return flot_pie_chart(
        array_values($chart_data),
        array_keys($chart_data),
        $width,
        $height,
        $water_mark_url,
        $font,
        $font_size,
        $legend_position,
        $colors,
        $hide_labels
    );
}


function ring_graph(
    $chart_data,
    $width,
    $height,
    $others_str='other',
    $homedir='',
    $water_mark='',
    $font='',
    $font_size='',
    $ttl=1,
    $legend_position=false,
    $colors='',
    $hide_labels=false,
    $background_color='white'
) {
    if (empty($chart_data)) {
        return graph_nodata_image($width, $height, 'pie');
    }

    setup_watermark($water_mark, $water_mark_file, $water_mark_url);

    // This library allows only 8 colors
    $max_values = 18;

    if ($ttl == 2) {
        $params = [
            'chart_data'       => $chart_data,
            'width'            => $width,
            'height'           => $height,
            'colors'           => $colors,
            'module_name_list' => $module_name_list,
            'long_index'       => $long_index,
            'no_data'          => $no_data,
            'water_mark'       => $water_mark,
            'font'             => $font,
            'font_size'        => $font_size,
            'unit'             => $unit,
            'ttl'              => $ttl,
            'homeurl'          => $homeurl,
            'background_color' => $background_color,
            'legend_position'  => $legend_position,
            'background_color' => $background_color,
        ];

        return generator_chart_to_pdf('ring_graph', $params);
    }

    return flot_custom_pie_chart(
        $chart_data,
        $width,
        $height,
        $colors,
        $module_name_list,
        $long_index,
        $no_data,
        false,
        '',
        $water_mark,
        $font,
        $font_size,
        $unit,
        $ttl,
        $homeurl,
        $background_color,
        $legend_position,
        $background_color
    );
}
