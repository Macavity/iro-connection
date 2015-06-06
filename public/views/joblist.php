<?php
/**
 * iRO Jobliste - Type I
 *
 * @package iRO_Connection
 */

$iRO_Jobs = iRO_Connection::getJobs();

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

                    <table id="jobList" class="" width="840" border="0" cellpadding="0" cellspacing="3" bordercolor="#CCCCCC">
                        <thead>
                        <tr>
                            <td width="60" class="schwarz_standard"><strong>Job-ID</strong></td>
                            <td width="218" class="schwarz_standard"><strong>Position</strong></td>
                            <td width="117" class="schwarz_standard"><strong>Branche</strong></td>
                            <td width="148" class="schwarz_standard"><strong>Standort</strong></td>
                            <td width="120" class="schwarz_standard"><strong>Berater</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($iRO_Jobs as $job){ ?>
                            <tr>
                                <td class="schwarz_standard"><?=$job['fm_id']?></td>
                                <td class="main">
                                    <a href="/job/<?=$job['lang']?>/<?=$job['rewrite_link']?>" class="blau_standard">
                                        <?=$job['position']?>
                                    </a>
                                </td>
                                <td class="schwarz_standard"><?=$job['industry']?></td>
                                <td class="schwarz_standard"><?=$job['location']?></td>
                                <td>
                                    <a href="mailto:<?=$job['mail']?>?subject=ReferenceCode-<?=$job['fm_id']?>" class="Stil2"><?=$job['contact']?></a>
                                    <span style="display:none;"><?=$job['full_text']?></span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div><!-- #content -->
        </div><!-- #primary -->
        <?php get_sidebar( 'content' ); ?>
    </div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
