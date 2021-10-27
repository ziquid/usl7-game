<?php

/**
 * Implements theme_placeholder().
 */
function cdc_games_placeholder($text) {
  return '<span class="nowrap highlight">' . check_plain($text) . '</span>';
}

/**
 * Implements template_preprocess_page.
 */
function NOT_cdc_games_preprocess_page(&$vars) {
  $game = $arg0 = drupal_html_class(arg(0));
  $page = $arg1 = drupal_html_class(arg(1));
  $phone_id = $arg2 = check_plain(arg(2));
  global $player_location_id;

  // Pretend usl_esa is stlouis for now.
  if ($game == 'usl-esa') {
    $game = 'stlouis';
  }

  // Game class.
  $vars['body_classes'] = str_replace('page-' . $arg0,
    'game-' . $game . ' page-' . $page, $vars['body_classes']);

  // Orientation class.
  if ((stripos($_SERVER['HTTP_USER_AGENT'], 'orientation=landscape') !== FALSE) ||
    (substr($phone_id, 0, 9) == 'landscape')) {
    $vars['body_classes'] .= ' landscape-orientation';
  }
  else {
    $vars['body_classes'] .= ' portrait-orientation';
  }

  if (strlen($player_location_id)) {
    $vars['body_classes'] .= ' location-' . $player_location_id;
  }
}
