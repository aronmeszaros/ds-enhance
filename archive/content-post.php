<?php

/**
 * Template part for displaying posts content in single.php
 *
 */
?>
<article id="post-<?php the_ID(); ?>" class="spacing-40">
  <?php if (has_post_thumbnail()): ?>
    <div class="post-thumbnail">
      <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
    </div>
  <?php endif; ?>
  <div class="container spacing-40">
    <header class="entry-header text-center">
      <div class="back-button-wrapper">
        <?php
        $categories = get_the_category();
        $href = esc_url(home_url('/blog'));
        if (!empty($categories)) {
          $main_category = $categories[0];
          $category_link = get_category_link($main_category->term_id);
          $href = esc_url($category_link);
        }
        ?>
        <a href="<?php echo $href; ?>" class="btn materialize-button outlined waves-effect waves-light btn-rounded btn-dark">
          <i class="material-icons left">arrow_back</i>
          <?php echo __('Späť', 'digiradca') ?>
        </a>
      </div>
      <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
      <p class="post-date">
        <?php echo get_the_date(); ?>
      </p>
    </header>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </div>
  <hr class="content-separator" />
  <div class="related-articles-section spacing-40">
    <?php
    // Get current post's primary category for related articles
    $categories = get_the_category();
    $category_slug = !empty($categories) ? $categories[0]->slug : 'nezaradene';

    // Render the DigiPosts carousel
    render_digi_posts_carousel([
      'heading' => 'Súvisiace témy',
      'category' => $category_slug,
      'show_description' => 'no',
      'show_date' => 'yes'
    ]);
    ?>
  </div>
</article>