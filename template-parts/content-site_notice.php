<?php
/**
 * The template part for displaying a site_notice card.
 *
 * @package Argon
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Render the custom card for the site notice.
// This function is defined in includes/site-notices.php
if (function_exists('apex_render_site_notice_card')) {
    apex_render_site_notice_card(get_post());
}
?>