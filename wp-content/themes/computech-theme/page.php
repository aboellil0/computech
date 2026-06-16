<?php
get_header();
while (have_posts()) : the_post();
    computech_breadcrumbs(get_the_title());
?>
<section class="page-content-section"><div class="container"><h1><?php the_title(); ?></h1><div class="page-content"><?php the_content(); ?></div></div></section>
<?php endwhile; get_footer(); ?>
