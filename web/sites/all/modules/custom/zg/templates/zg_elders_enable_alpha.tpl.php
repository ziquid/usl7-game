<?php

/**
 * @file
 * Template for enabling/disabling prerelease/alpha access.
 *
 * Synced with CG: N/A
 * Synced with 2114: N/A
 * Ready for phpcbf: done
 * Ready for MVC separation: no
 * Controller moved to callback include: no
 * View only in theme template: no
 * All db queries in controller: no
 * Minimal function calls in view: no
 * Removal of globals: no
 * Removal of game_defs include: no
 * .
 */

/* ------ CONTROLLER ------ */
global $game, $phone_id;

include drupal_get_path('module', 'zg') . '/includes/' . $game . '_defs.inc';
$game_user = zg_fetch_user();

// User chose to toggle!
if ($arg3 == 'yes') {
  zg_set_value($game_user, 'enabled_alpha', !zg_get_value($game_user, 'enabled_alpha'));
  db_set_active();
  drupal_goto("/$game/elders/$arg2");
}

if (zg_get_value($game_user, 'enabled_alpha')) {
  $enable = 'Disable';
}
else {
  $enable = 'Enable';
}
$enable_lower = drupal_strtolower($enable);

// Did the player enter a code?  Save it.
if (strlen($code = $_GET['code'])) {
  if ($code[0] === '-') {
    // Allow for removal of codes already set.
    zg_remove_value($game_user, substr($code, 1));
    $code_response = '<div class="system-message-response">' .
      t('Your code has been removed.') . '</div>';
  }
  zg_set_value($game_user, $code);
  $code_response = '<div class="system-message-response">' .
    t('Code %code has been activated.', ['%code' => $code]) . '</div>';
}
else {
  $code_response = '';
}

/* ------ VIEW ------ */
zg_fetch_header($game_user);
db_set_active();
?>
<?php print $code_response; ?>
<div class="title">
  <?php print $enable; ?> Alpha access?
</div>
<div class="subtitle">
  Do you really want to <?php print $enable_lower; ?> pre-release (alpha) access?
</div>
<p>
  Enabling pre-release features may give you early access to new features but
  <em>they may not be stable!</em>  In fact, <em>they may not work at all!</em>
</p>
<p>
  If you are unsure, disabling pre-release features is the safer choice.
</p>

<div class="elders-menu big">
  <div class="menu-option">
    <a href="/<?php print $game; ?>/elders_enable_alpha/<?php print $arg2; ?>/yes">
      Yes, I want to <?php print $enable_lower; ?> pre-release (Alpha) features
    </a>
  </div>
  <div class="menu-option">
    <a href="/<?php print $game; ?>/elders/<?php print $arg2; ?>">
      No, I prefer to keep things the way they are
    </a>
  </div>
</div>

<div class="alpha-only">
  <p>
    If you enable pre-release features, you can further customize
    your game experience by entering a code here:
  </p>

  <form method="get" action="" class="ask-name">
    <input type="text" name="code" width="20" maxlength="20">
    <input class="crafting-submit-button" type="submit" value="Submit">
  </form>
</div>
