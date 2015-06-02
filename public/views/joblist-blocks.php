<?php
/**
 * iRO Jobliste - Type II
 *
 * @package iRO_Connection
 */

$apiDomain = iRO_Connection::get_api_domain();

$iroSerial = iRO_Connection::get_serial();

/*
 * Load Jobs from H2H
 */

$curlUrl = $apiDomain.'/data/'.$iroSerial.'/jobs/all';

$curlHandle = curl_init($curlUrl);
curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

$requestData = curl_exec($curlHandle);

curl_close($curlHandle);

$jsonData = json_decode($requestData, true);

$joblist = array();

if(isset($jsonData['results'])){
    $joblist = $jsonData['results'];
}


get_header(); ?>

    <div id="main-content" class="main-content">

        <div id="primary" class="content-area">
            <div id="content" class="site-content" role="main">
                <h2><span class="demi"><?php the_title(); ?></span></h2>
                <?php the_content(); ?>

                <?php
                if ( have_posts() ) :
                    // Start the Loop.
                    while ( have_posts() ) : the_post();

                        get_template_part( 'content', get_post_format() );

                    endwhile;
                    // Previous/next post navigation.
                    //twentyfourteen_paging_nav();
                else :
                    // If no content, include the "No posts found" template.
                    get_template_part( 'content', 'none' );

                endif;
                ?>
                <div id="jobs-wrapper" class="entry-content">
                    <?php foreach($joblist as $job){ ?>
                        <div class="job-wrapper">
                            <div class="job-title">
                                <a href="/job/<?=$job['lang']?>/<?=$job['rewrite_link']?>"><?=$job['position']?></a>
                            </div>
                            <?php ?>
                            <div class="job-location"><?=__("Standort:", "adrianroth")?> <?=$job['location']?>,&nbsp;</div>
                            <div class="job-id">Job Id: <?=$job['fm_id']?></div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- #content -->
        </div><!-- #primary -->
        <?php get_sidebar( 'content' ); ?>
    </div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
