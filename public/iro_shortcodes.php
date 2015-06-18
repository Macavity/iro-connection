<?php

function iRO_Connection_shortcodeJobsCount($atts, $content = ""){

    extract(
        shortcode_atts(
            array(
                'type' => 'open',
            ),
            $atts )
    );

    return '<span class="jsIroJobCount">'.iRO_Connection::getJobsCount($type).'</span>';

}

add_shortcode( 'iro_jobcount', 'iRO_Connection_shortcodeJobsCount' );

