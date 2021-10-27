<?php

/**
 * @file
 * The game's main screen.
 *
 * Synced with CG: yes
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

$version = 'v0.9.6, Feb 17 2020';

global $game, $phone_id;
include drupal_get_path('module', 'zg') . '/includes/' . $game . '_defs.inc';
$game_user = zg_fetch_user();
$message = check_plain($_GET['message']);

$d = zg_get_html(
  [
    'tagline',
  ]
);

zg_competency_gain($game_user, 'where the heart is');

if (substr($phone_id, 0, 3) == 'ai-') {
  echo "<!--\n<ai \"home\"/>\n-->";
}

$today = date('Y-m-d');

$sql = 'select residents, rating from neighborhoods where id = %d;';
$result = db_query($sql, $game_user->fkey_neighborhoods_id);
$item = db_fetch_object($result);
$beauty = (int) $item->rating;

if ($game_user->last_bonus_date != $today || $game_user->meta == 'admin') {
  $money = ($game_user->level * $item->residents) + $game_user->income -
    $game_user->expenses;
  $extra_bonus = '';

/*
  if ($game == 'stlouis') {

    $sql = 'select quantity from staff_ownership
      where fkey_staff_id = 18 and fkey_users_id = %d;';
    $result = db_query($sql, $game_user->id);
    $item = db_fetch_object($result);

    if ($item->quantity >= 1) {
      $money *= 3;
      $extra_text .= '<div class="level-up-text">
        ~ Your private banker tripled your bonus ~
      </div>';
    }

  }

  if ($game == 'celestial_glory') {

    if ($game_user->fkey_values_id == 5) {
      $money *= 1.01;
      $extra_text .= '<div class="level-up-text">
        ~ As a Merchant, you gained an extra 1% ~
      </div>';
    }

  }
*/
  $money = ceil($money);

firep("adding $money money because last_bonus_date = $last_bonus_date");

  $sql = 'update users set money = money + %d, last_bonus_date = "%s"
    where id = %d;';
  $result = db_query($sql, $money, $today, $game_user->id);
  $game_user = zg_fetch_user();

  $extra_bonus = '<div class="speech-bubble-wrapper background-color">
  <div class="wise_old_man happy">
  </div>
  <div class="speech-bubble">
    <p class="bonus-text">Daily Bonus!</p>
    <p>You have received <strong>' .
    number_format($money) . ' ' . $game_user->values . '</strong>!</p>' .
    $extra_text .
      '<p>For the next three minutes, competencies can be enhanced every 15 seconds.</p>
      <p>Come back tomorrow for another bonus!</p>
    </div>
  </div>';

  // Fast comps for the next three minutes.
  zg_set_timer($game_user, 'fast_comps_15', 180);

  // Add competency for work.
  $sql = 'SELECT land.fkey_enhanced_competencies_id FROM `land`
  left join land_ownership on land_ownership.fkey_land_id = land.id
  left join users on users.id = land_ownership.fkey_users_id
  WHERE users.id = %d and land.type = "job"';
  $result = db_query($sql, $game_user->id);
  $item = db_fetch_object($result);

  if (isset($item->fkey_enhanced_competencies_id)) {
    zg_competency_gain($game_user, (int) $item->fkey_enhanced_competencies_id);
  }

}

// Get values of current hood's alder.
$sql = 'SELECT `values` FROM `users`
  inner join elected_officials on elected_officials.fkey_users_id = users.id
  WHERE fkey_neighborhoods_id = %d
  and elected_officials.fkey_elected_positions_id = 1;';
$result = db_query($sql, $game_user->fkey_neighborhoods_id);
$alder = db_fetch_object($result);
firep($alder, 'values of current hood alder');
$alder_values = drupal_strtolower($alder->values);
$player_values = drupal_strtolower($game_user->values);

zg_fetch_header($game_user);

// AI bot?  No reason to spend cycles on this.
if (substr($phone_id, 0, 3) == 'ai-') {
  db_set_active();
  return;
}

if (empty($game_user->referral_code)) {
  $good_code = FALSE;
  $count = 0;

  while (!$good_code && $count++ < 10) {
    $referral_code = '0000' .
      base_convert(mt_rand(0, pow(36, 5) - 1) . '', 10,
        36);
    $referral_code = strtoupper(substr($referral_code,
      strlen($referral_code) - 5, 5));
firep($referral_code);

    $sql = 'select referral_code from users where referral_code = "%s";';
    $result = db_query($sql, $referral_code);
    $item = db_fetch_object($result);

    // Code not already in use - use it!
    if (empty($item->referral_code)) {
      $good_code = TRUE;
      $sql = 'update users set referral_code = "%s" where id = %d;';
      $result = db_query($sql, $referral_code, $game_user->id);
      $game_user->referral_code = $referral_code;
    }
  }
}

if (($today == '2019-12-26')) {
  $extra_menu = '-boxing';
}

$event_text = '';

switch($event_type) {

  case EVENT_DONE:

    $event_text = '<div class="event">
      The event is over!&nbsp; We hope you had fun.
      </div><div class="event-tagline small">
        <a href="/' . $game . '/top_event_points/' . $arg2 .
          '">Leaderboard</a>
      </div>';
    break;

  case EVENT_DEBATE:

    $event_text = '<div class="event">
        While we are waiting on ToxiCorp to be ready,
        let\'s have a debate mini event.&nbsp; Debate for prizes today!
      </div><div class="event-tagline small">
        <a href="/' . $game . '/top_event_points/' . $arg2 .
          '">Leaderboard</a>
      </div>';
    break;

  case EVENT_PRE_MAY:

    $event_text = '<div class="event">
<div class="event-title">
        May\'s Quest
      </div>
      <div class="event-tagline">
        ~ Find the perfect gift for Mother\'s Day ~
      </div>
      <div class="event-text">
        Starts May 1
      </div>
      <!--<div class="event-tagline small">
        <a href="/' . $game . '/top_event_points/' . $arg2 .
      '">Leaderboard</a>
      </div>-->
      </div>';
    break;

  case EVENT_CINCO_DE_MAYO:

    $event_text = '<div class="event">
      <div class="event-tagline">
        <!--Are you going to the Cinco De Mayo party in Benton Park West?-->
        Didn\'t finish the Cinco De Mayo event in Benton Park West?
      </div>
      <div class="event-text">
        <!--I hear it\'s going to be fun!-->
        Try it again this weekend! (Fri, Sat, Sun)
      </div>
      </div>';
    break;
}

// Monthly quests.
switch ($month_mission) {

  case MISSION_MAY:
    $event_text .= '<div class="event">
<div class="event-title">
        May\'s Quest
      </div>
      <div class="event-tagline">
        ~ Find the perfect gift for your wife ~
      </div>
      <div class="event-tagline">
        to celebrate Mother\'s Day
      </div>
      <div class="event-text">
        Ends May 13
      </div>
      <div class="event-text">
        <a href="/' . $game . '/quests/' . $arg2 .
          '/1005">Start Here</a>
      </div>
      <!--<div class="event-tagline small">
        <a href="/' . $game . '/top_event_points/' . $arg2 .
    '">Leaderboard</a>
      </div>-->
      </div>';
    break;

  case MISSION_JUN:
    $event_text .= '<div class="event">
<div class="event-title">
        June\'s Quest
      </div>
      <div class="event-tagline">
        ~ Find the perfect tie for your husband ~
      </div>
      <div class="event-tagline">
        to celebrate Father\'s Day
      </div>
      <div class="event-text">
        Ends June 24
      </div>
      <div class="event-text">
        <a href="/' . $game . '/quests/' . $arg2 .
      '/1006">Start Here</a>
      </div>
      <!--<div class="event-tagline small">
        <a href="/' . $game . '/top_event_points/' . $arg2 .
      '">Leaderboard</a>
      </div>-->
      </div>';
    break;
}

zg_alter('homepage_event_notice', $game_user, $event_text);
zg_alter('homepage_menu', $game_user, $extra_menu);

$link = 'quest_groups';
$lqg = zg_fetch_latest_quest_group($game_user);
$show_expanded = ($game_user->level < 7) ? '?show_expanded=0' : '';

if ($game_user->fkey_clans_id > 0) {
  $clan_link = "clan_list/$arg2/{$game_user->fkey_clans_id}";
}
else {
  $clan_link = "clan_list_available/$arg2";
}

$debates_class = drupal_html_class($game_text['menu']['debates']) . '-menu';
$data = zg_get_msgs($game_user);
zg_alter('homepage_messages', $game_user, $data);
$msg_shown = FALSE;

echo <<< EOF
<div class="title">
  <img src="/sites/default/files/images/{$game}_title.png">
</div>
<div class="tagline">
  {$d['tagline']}
</div>
<a class="version" href="/$game/changelog/$arg2">
  $version
</a>
$extra_bonus
<div class="new-main-menu">
  <img src="/sites/default/files/images/{$game}_home_menu{$extra_menu}.jpg">
    <ul class="value-links">
      <li>
        <span class="beauty value first odd">
          $beauty
        </span>
        <span class="beauty label first odd">
          BTY
        </span>
      </li>
      <li>
        <span class="chaos value even">
          ???
        </span>
        <span class="chaos label even">
          CHA
        </span>
      </li>
      <li>
        <span class="faith value odd">
          ???
        </span>
        <span class="faith label odd">
          FTH
        </span>
      </li>
      <li>
        <span class="finance value even">
          ???
        </span>
        <span class="finance label even">
          FIN
        </span>
      </li>
      <li>
        <span class="health value odd">
          ???
        </span>
        <span class="health label odd">
          HEA
        </span>
      </li>
      <li>
        <span class="intelligence value even">
          ???
        </span>
        <span class="intelligence label even">
          INT
        </span>
      </li>
      <li>
        <span class="strength value odd last">
          ???
        </span>
        <span class="strength label odd last">
          STR
        </span>
      </li>
    </ul>
    <ul class="menu-links">

      <li>
        <a class="actions-menu" href="/$game/actions/$arg2">
          {$game_text['menu']['actions']}
        </a>
      </li>

      <li>
        <a class="aides-menu" href="/$game/land/$arg2">
          {$game_text['menu']['aides']}
        </a>
      </li>

      <li>
        <a class="clan-menu" href="/$game/$clan_link">
          {$game_text['menu']['clan']}
        </a>
      </li>

      <li>
        <a class="$debates_class" href="/$game/debates/$arg2">
          {$game_text['menu']['debates']}
        </a>
      </li>

      <li>
        <a class="elders-menu" href="/$game/elders/$arg2">
          {$game_text['menu']['elders']}
        </a>
      </li>

      <li>
        <a class="elections-menu" href="/$game/elections/$arg2">
          {$game_text['menu']['elections']}
        </a>
      </li>

      <li>
        <a class="forum-menu" href="external://discord.gg/cFyt7w9">
          {$game_text['menu']['forum']}
        </a>
      </li>

      <li>
        <a class="help-menu" href="/$game/help/$arg2">
          {$game_text['menu']['help']}
        </a>
      </li>

      <li>
        <a class="missions-menu"
          href="/$game/$link/$arg2{$show_expanded}#group-{$lqg}">
          {$game_text['menu']['missions']}
        </a>
      </li>

      <li>
        <a class="move-menu" href="/$game/move/$arg2/0">
          {$game_text['menu']['move']}
        </a>
      </li>

      <li>
        <a class="profile-menu" href="/$game/user/$arg2">
          {$game_text['menu']['profile']}
        </a>
      </li>

    </ul>
  </div>
  <div class="location">
    <span class="location-$alder_values player-$player_values">$game_user->location</span>
  </div>
  $event_text
  <div class="news">
    <div class="title">
      News
    </div>
    <div class="news-buttons">
      <button id="news-all" class="active">All</button>
      <button id="news-user">Personal</button>
      <button id="news-challenge">{$election_tab}</button>
      <button id="news-clan">$party_small</button>
      <button id="news-mayor">${game_text['mayor_tab']}</button>
    </div>
    <div id="all-text">
      <div class="news-item clan clan-msg">
        <div class="message-title">Send a message to your clan</div>
        <div class="send-message">
        <form method=get action="/$game/party_msg/$arg2">
          <textarea class="message-textarea" name="message" rows="2">$message</textarea>
          <br>
          <div class="send-message-target">
            <select name="target">
EOF;

if ($game_user->fkey_clans_id) {
  echo '<option value="clan">Clan</option>';
}
if ($game_user->can_broadcast_to_party || $game_user->meta == 'admin') {
  echo '<option value="neighborhood">' . $hood . '</option>';
}

echo <<< EOF
              <option value="values">$party</option>
            </select>
          </div>
          <div class="send-message-send-wrapper">
            <input class="send-message-send" type="submit" value="Send"/>
          </div>
        </form>
      </div>
    </div>
EOF;

foreach ($data as $item) {
// firep($item);

  $display_time = zg_format_date(strtotime($item->timestamp));
  $clan_acronym = '';

  if (!empty($item->clan_acronym)) {
    $clan_acronym = "($item->clan_acronym)";
  }

  if ($item->is_clan_leader) {
    $clan_acronym .= '*';
  }

  if ($item->private) {
    $private_css = 'private';
    $private_text = '(private)';
  }
  else {
    $private_css = $private_text = '';
  }

  $private_css .= ' ' . $item->type . ' ' . $item->type . '-' . $item->subtype;

  if (empty($item->username)) {
    $username = '';
    $reply = '';
  }
  else {
    $username = 'from ' . $item->ep_name . ' ' . $item->username . ' ' .
      $clan_acronym;
    if (!in_array($item->username, ['USLCE Game', 'The Socialite'])) {
      $reply = '<div class="message-reply-wrapper"><div class="message-reply">
        <a href="/' . $game . '/user/' . $arg2 . '/' . $item->phone_id .
        '" class="button">View / Respond</a></div></div>';
      $reply = zg_render_button('user', 'View / Respond', '/' .
        $item->phone_id);
    }
    else {
      $reply = '';
    }
  }

  echo <<< EOF
    <div class="news-item $item->type">
      <div class="dateline">
        $display_time $username $private_text
      </div>
      <div class="message-body $private_css">
        <p>$item->message</p>$reply
      </div>
    </div>
EOF;
  $msg_shown = TRUE;

}

echo <<< EOF
  </div>
</div>
<script type="text/javascript">
var isoNews = $('#all-text').isotope({
itemSelector: '.news-item',
layoutMode: 'fitRows'
});

$("#news-all").bind("click", function() {
isoNews.isotope({ filter: "*:not(.clan-msg)" });
$(".news-buttons button").removeClass("active");
$("#news-all").addClass("active");
});

$('#news-user').bind('click', function() {
isoNews.isotope({ filter: ".user" });
$(".news-buttons button").removeClass("active");
$("#news-user").addClass("active");
});

$('#news-challenge').bind('click', function() {
isoNews.isotope({ filter: ".challenge" });
$(".news-buttons button").removeClass("active");
$("#news-challenge").addClass("active");
});

$('#news-clan').bind('click', function() {
isoNews.isotope({ filter: ".hood, .clan, .values" });
$(".news-buttons button").removeClass("active");
$("#news-clan").addClass("active");
});

$('#news-mayor').bind('click', function() {
isoNews.isotope({ filter: ".mayor" });
$(".news-buttons button").removeClass("active");
$("#news-mayor").addClass("active");
});
</script>
<!--  <div id="personal-text">-->
EOF;

db_set_active();
