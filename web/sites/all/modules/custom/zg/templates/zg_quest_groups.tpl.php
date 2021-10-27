<?php

/**
 * @file
 * List of quest groups.
 *
 * Synced with CG: N/A
 * Synced with 2114: N/A
 * Ready for phpcbf: done!
 * Ready for MVC separation: done
 * Controller moved to callback include: no
 * View only in theme template: no
 * All db queries in controller: no
 * Minimal function calls in view: no
 * Removal of globals: no
 * Removal of game_defs include: no
 * .
 */

global $game, $phone_id;

/* ------ CONTROLLER ------ */
include drupal_get_path('module', 'zg') . '/includes/' . $game . '_defs.inc';
$game_user = zg_fetch_user();
zg_slack($game_user, 'pages', 'quests',
  "\"Quests\" for Player \"$game_user->username\".");
$quest_groups = zg_fetch_quest_groups($game_user);

if (strlen($expanded = $_GET['show_expanded'])) {
  $quest_groups[$expanded]->showExpanded = TRUE;
}

/* ------ VIEW ------ */
zg_fetch_header($game_user);
?>

<div class="swiper-container">
  <div class="swiper-wrapper">
    <?php foreach ($quest_groups as $quest_group): ?>
      <?php zg_show_quest_group_slide($game_user, $quest_group); ?>
    <?php endforeach; ?>
  </div>
  <div class="swiper-pagination <?php print $quest_groups[0]->swiperPaginationClasses; ?>"></div>
  <div class="swiper-button-prev <?php print $quest_groups[0]->swiperArrowClasses; ?>"></div>
  <div class="swiper-button-next <?php print $quest_groups[0]->swiperArrowClasses; ?>"></div>
</div>

<?php
db_set_active();
