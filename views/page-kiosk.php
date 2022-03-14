<?php
// Get header
wp_head();
?>

<main>
	<?php echo do_shortcode('[interactive-map]', false); ?>
</main>

<?php
// Get footer
wp_footer();
?>

<style>
	.rc-anchor-content,
	#cookie-law-info-again,
	#rc-anchor-container,
	#cookie-law-info-bar {
		display: none !important;
	}
</style>