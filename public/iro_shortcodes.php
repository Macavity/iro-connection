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

function iRO_Connection_shortcodeJobFilter(){

    $placeholder = __("To search type and hit enter", "pape");

    return '<input type="text" name="jobsearch_filter" id="jobsearch_filter" class="form-wrapper" placeholder="'.$placeholder.'" onfocus="if(this.value==this.defaultValue)this.value=\'\';" onblur="if(this.value==\'\')this.value=this.defaultValue;"/>';
}

add_shortcode( 'iro_jobcount', 'iRO_Connection_shortcodeJobsCount' );
add_shortcode( 'iro_jobfilter', 'iRO_Connection_shortcodeJobFilter' );

