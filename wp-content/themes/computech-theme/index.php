<?php
get_header();
if (have_posts()) :
    echo '<section class="page-content-section"><div class="container">';
    while (have_posts()) : the_post();
        echo '<article>'; the_title('<h2>','</h2>'); the_excerpt(); echo '</article>';
    endwhile;
    the_posts_pagination();
    echo '</div></section>';
else :
    echo '<section class="page-content-section"><div class="container"><h1>لا يوجد محتوى</h1></div></section>';
endif;
get_footer();
