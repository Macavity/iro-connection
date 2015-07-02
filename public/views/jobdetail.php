<?php
/**
 * iRO Jobdetail
 *
 * @package iRO_Connection
 */

/** @var $iro_jobId */
/** @var $iro_job */

$iro_jobId = get_query_var("job_id");

$iro_job = iRO_Connection::getJobDetail($iro_jobId);

get_header(); ?>

    <div id="main-content" class="main-content">

        <div id="primary" class="content-area">
            <div id="content" class="site-content" role="main">

                <div id="jobs-wrapper" class="entry-content">

                    <?php if(empty($iro_job)){ ?>

                    <h1>Jobdetails</h1>
                    <div class="main">
                        Es wurde kein aktiver Job mit dieser Referenznummer gefunden.<br>
                        There is no valid job offer enlisted with this reference id currently.
                    </div>
                    <?php } else { ?>

                        <?php
                            the_content();
                            the_post();
                        ?>
                        <?php if(!empty($iro_job['logo_src'])) { ?>
                            <p>&nbsp;</p>
                            <p align="center"><img src="<?=$iro_job['logo_src']?>"/></p>
                            <p>&nbsp;</p>
                        <?php } ?>
                        <p><?=$iro_job['job_intro']?></p>

                        <h1>Position: <?php echo $iro_job['position']?></h1>

                        <p>&nbsp;</p>
                        <p>Projektnummer: <?php echo $iro_job['fm_id']?></p>
                        <p>Position: <?php echo $iro_job['position']?></p>

                    <div class="jobheader"><!-- --></div>
                    <div class="main">
                        <p>
                            <br>
                            <b><span class="subheader"><?php echo __('City')?></span></b>: <?php echo $iro_job['location']?><br>
                            <b><span class="subheader"><?php echo __('Industry')?></span></b>: <?php echo $iro_job['industry']?>
                        </p>
                    </div>
                    <div class="main">
                        <p>
                            <br>
                            <span class="subheader"><b><?php echo __("The Position")?></b></span><br>
                        <ul><?php echo $iro_job['job_description']?></ul>
                        </p>
                        <p>&nbsp;</p>
                    </div>
                    <div class="main">
                        <span class="subheader"><b><?php echo __("The ideal candidate")?></b></span><br>
                        <ul><?php echo $iro_job['job_candidate']?></ul>
                    </div>

                    <div class="main">
                        <span class="subheader"><b><?php echo __("Why is this position desirable")?></b></span><br>
                        <ul><?php echo $iro_job['job_desirability']?></ul>
                    </div>

                    <div class="main">
                        <p><?php echo $iro_job['job_resume']?></p>
                        <p>
                            <span class="Stil2">E-Mail an</span>: <a href="mailto:<?php echo $iro_job['contact_mail']?>?subject=ReferenceCode-<?php echo $iro_job['fm_id']?>"><?php echo $iro_job['contact_name']?></a>
                        </p>
                    </div>

                    <?php } ?>
                    <br><br>
                </div>
            </div><!-- #content -->
        </div><!-- #primary -->
        <?php get_sidebar( 'content' ); ?>
    </div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
