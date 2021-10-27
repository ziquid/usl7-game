<?php

/**
 * @file
 * Debates list.
 *
 * Synced with CG: no
 * Synced with 2114: no
 * Ready for phpcbf: no
 * Ready for MVC separation: no
 * Controller moved to callback include: no
 * View only in theme template: no
 * All db queries in controller: no
 * Minimal function calls in view: no
 * Removal of globals: no
 * Removal of game_defs include: no
 * .
 */

global $game, $phone_id;
include drupal_get_path('module', 'zg') . '/includes/' . $game . '_defs.inc';
$game_user = zg_fetch_user();

if (empty($game_user->username) || $game_user->username == '(new player)') {
  db_set_active();
  drupal_goto($game . '/choose_name/' . $arg2);
}

zg_slack($game_user, 'pages', 'debates_list',
  "\"Debates\" for Player \"$game_user->username\".");
zg_fetch_header($game_user);
zg_show_elections_menu($game_user);

// Do AI moves from this page.
if (mt_rand(0, 5) == 1 || $game_user->meta == 'toxiboss' || $game_user->meta == 'admin') {
  zg_move_ai();
}

if (!$game_user->seen_neighborhood_quests) {

  // Intro neighborhood quests == debates, if they haven't been shown.
  if ($game == 'celestial_glory') {

    echo <<< EOF
<p>&nbsp;</p>
<div class="welcome">
<div class="wise_old_man_small">
</div>
<p>&quot;As you journey, you will meet others who like to challenge.&nbsp;
Touch any player's name to challenge them.</p>
<p class="second">&quot;The more $elocution you have, the better you
will do in these challenges.</p>
<p></p>
</div>
EOF;

    $sql = 'update users set seen_neighborhood_quests = 1 where id = %d;';
    $result = db_query($sql, $game_user->id);
  }
}

if ($game_user->level < 15) {

  echo <<< EOF
<ul>
<li>Win {$debate_lower}s to give you more $game_user->values and $experience</li>
<li>Each $debate_lower costs one Action</li>
<li>Wait and rest for a few minutes if you run out of Actions</li>
</ul>
EOF;

}

// Boxing day?  Check for gloves.
if ($debate == 'Box') {

  $sql = 'select quantity from equipment_ownership
    where fkey_equipment_id = %d and fkey_users_id = %d;';
  $result = db_query($sql, 79, $game_user->id);
  $item = db_fetch_object($result);

  // No boxing gloves!
  if ($item->quantity < 1) {

    echo <<< EOF
<div class="title">
No boxing gloves?
</div>
<div class="subtitle">
How can you box without gloves?
</div>
EOF;
    zg_button('home', 'Go Home Instead');
    db_set_active();
    return;
  }
}

echo <<< EOF
<div class="title">
  Whom would you like to $debate_lower?
</div>
EOF;

//  $debate_wait_time = 1200;
//  if ($debate == 'Box') $debate_wait_time = 900;

$data = [];
$sql = 'SELECT username, experience, `values`.party_title, `values`.party_icon,
  users.id, users.phone_id, clan_members.is_clan_leader, users.meta,
  clans.acronym AS clan_acronym, neighborhoods.name as neighborhood
  FROM users
  LEFT JOIN `values` ON users.fkey_values_id = `values`.id
  LEFT OUTER JOIN clan_members ON clan_members.fkey_users_id = users.id
  LEFT OUTER JOIN clans ON clan_members.fkey_clans_id = clans.id
  LEFT OUTER JOIN neighborhoods
    ON users.fkey_neighborhoods_id = neighborhoods.id
  WHERE users.id <> %d
  AND (clans.id <> %d OR clans.id IS NULL OR users.meta = "zombie")
  AND username <> ""
  AND (debates_last_time < "%s" OR
   (users.meta = "zombie" AND debates_last_time < "%s"))
  AND users.level > %d
  AND users.level < %d
  ORDER BY abs(users.experience - %d) ASC
  LIMIT 12;'; // and users.fkey_neighborhoods_id = %d
$sql = 'SELECT users.id
  FROM users
  LEFT JOIN `values` ON users.fkey_values_id = `values`.id
  LEFT OUTER JOIN clan_members ON clan_members.fkey_users_id = users.id
  LEFT OUTER JOIN clans ON clan_members.fkey_clans_id = clans.id
  LEFT OUTER JOIN neighborhoods
    ON users.fkey_neighborhoods_id = neighborhoods.id
  WHERE users.id <> %d
  AND (clans.id <> %d OR clans.id IS NULL OR users.meta = "zombie")
  AND username <> ""
  AND (debates_last_time < "%s" OR
   (users.meta = "zombie" AND debates_last_time < "%s"))
  AND users.level > %d
  AND users.level < %d
  ORDER BY abs(users.experience - %d) ASC
  LIMIT 12;'; // and users.fkey_neighborhoods_id = %d
$result = db_query($sql, $game_user->id, $game_user->fkey_clans_id,
  date('Y-m-d H:i:s', REQUEST_TIME - $debate_wait_time),
  date('Y-m-d H:i:s', REQUEST_TIME - $zombie_debate_wait),
  $game_user->level - 15,
  $game_user->level + 15, $game_user->experience);

// Jwc flag day - make debates much more active.
while ($item = db_fetch_object($result)) {
  $data[] = (int) $item->id;
}
$users = zg_fetch_users_by_ids($data);
db_set_active();

echo <<< EOF
<div class="elections-header">
  <div class="election-details">
    <div class="clan-title">$party</div>
    <div class="opponent-name">Name</div>
    <div class="opponent-influence">Action</div>
  </div>
</div>
<div class="elections">
EOF;

foreach ($users as $item) {
  zg_alter('debates_list', $game_user, $item);
firep($item, 'player to debate');

  if ($item->id == $game_user->id) {
    $clan_class = 'election-details me';
  }
  else {
    $clan_class = 'election-details';
  }

  $action = $debate;

  $button_debate = zg_render_button('debates_challenge', $action, '/' . $item->id);
  $button_view = zg_render_button('user', 'View', '/id:' . $item->id);
  print '<div class="' . $clan_class . '">' . zg_render_user($item, 'debates_list')
  . $button_debate . /*$button_view . */'</div>';

}
?>
</div>


