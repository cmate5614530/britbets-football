<?php
/**
 * This is the custom template file shown when displaying an event
 *
 */
get_header();
?>
<section id="content-area">
  <main id="main-content">
    <?php
      the_team();
    ?>
  </main>
  <?php get_sidebar(); ?>
</section>
<?php
get_footer();
