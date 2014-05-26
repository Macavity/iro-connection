<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Paneon_iRO
 * @author    Alexander Pape <a.pape@paneon.de>
 * @license   GPL-2.0+
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields('iro_plugin_options'); ?>
        <?php do_settings_sections('iro_plugin'); ?>

        <?php @submit_button(); ?>
    </form>
</div>
