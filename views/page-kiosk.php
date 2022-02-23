<?php
// Get header
wp_head();
?>

<main>
	<?php echo do_shortcode('[interactive-map]', false ) ;?>
</main>

<?php 
// Get footer
wp_footer();
?>