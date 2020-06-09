<div class="rpress-title-holder">
  <?php $item_prop = rpress_add_schema_microdata() ? 
  ' itemprop="name"' : ''; ?>
  <h3<?php echo $item_prop; ?> class="rpress_fooditem_title">
    <span class="food-title"><?php the_title();?></span>
  </h3>
  <?php $excerpt_length =  apply_filters( 'rpress_excerpt_length', 28 ); ?>
  <?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="description"' : ''; ?>
  
  <?php if ( has_excerpt() ) : ?>
    <div<?php echo $item_prop; ?> class="rpress_fooditem_excerpt">
      <?php echo apply_filters( 'rpress_fooditems_excerpt', wp_trim_words( get_post_field( 'post_excerpt', get_the_ID() ), $excerpt_length ) ); ?>
    </div>
  <?php elseif ( get_the_content() ) : ?>
    <div<?php echo $item_prop; ?> class="rpress_fooditem_excerpt">
      <?php echo apply_filters( 'rpress_fooditems_excerpt', wp_trim_words( get_post_field( 'post_content', get_the_ID() ), $excerpt_length ) ); ?>
    </div>
  <?php endif; ?>
  
</div>
