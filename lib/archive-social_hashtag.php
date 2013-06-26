<?php get_header(); ?>
<section id="social_hashtags">
	<?php if(have_posts()): ?>
		<ul>
		<?php while(have_posts()): the_post(); ?>
			<li><?php the_content(); ?></li>
		<?php endwhile; ?>
		</ul>
	<?php else: ?>
		<h4>No Content was found</h4>
	<?php endif; ?>
</section>
<?php get_footer(); ?>