Drupal.behaviors.zg = function (context) {

  function zg_header_check_message_count(url) {
    messageCount = 0;
    $.ajax({
      url: url,
      success: function (data, status, xhr) {
        messageCount = data;
      },
      complete: function (xhr, status) {
        if (messageCount > 9) {
          $("#msg-badge").text("9+");
        }
        else if (messageCount > 0) {
          $("#msg-badge").text(messageCount);
        }
        else {
          $("#msg-badge").text("");
        }
      }
    });
  }

  if (Drupal.settings.zg) {
    if (Drupal.settings.zg.party_icon) {
      $('body').addClass('party-' + Drupal.settings.zg.party_icon);
    }

    if (Drupal.settings.zg.enabled_alpha) {
      $('body').addClass('alpha');
    }

    if (Drupal.settings.zg.check_message_count_url) {
      console.log(Drupal.settings.zg.check_message_count_url);
      setInterval(zg_header_check_message_count, 2020, Drupal.settings.zg.check_message_count_url);
      setTimeout(zg_header_check_message_count, 20, Drupal.settings.zg.check_message_count_url);
    }

    if (Drupal.settings.zg.snowstorm) {
      snowStorm.autoStart = true;
      snowStorm.excludeMobile = false;
      snowStorm.className = "snowMobileFoo";
      snowStorm.animationInterval = 100;
      snowStorm.flakesMax = 60;
      snowStorm.flakesMaxActive = 40;
      snowStorm.snowColor = '#777777';
      snowStorm.snowCharacter = '❄';
      snowStorm.flakeWidth = 14;
      snowStorm.flakeHeight = 14;
      snowStorm.useMeltEffect = true;
      // snowStorm.useTwinkleEffect = true;
    }

    var level = parseInt(Drupal.settings.zg.level);
    // level = 1;
    var red = Math.max(level - 100, 0);
    var green = Math.max(Math.floor(100 - level), 0);
    var blue = level;
    if (blue > 100) {
      blue = 200 - blue;
    }
    red = Math.floor(red * 0.6);
    green = Math.floor(green * 0.6);
    blue = Math.floor(blue * 0.6);
    // $('body.game-stlouis').css('background-color', 'rgb(' + red + ', ' + green + ', ' + blue + ')');
    // $('.background-color').css('background-color', 'rgb(' + red + ', ' + green + ', ' + blue + ')');
  }

  // Menu button.
  $('#menu-button-1').click(function () {
    var xPos = self.pageXOffset;
    var yPos = self.pageYOffset;
    if (xPos + yPos) {
      window.scrollTo({top: 0, left: 0, behavior: 'smooth'});
    }
    else {
      window.location.href = $(this).attr('data-home-link');
    }
  });

  // Menu toggle.
  $('#menu-toggle').click(function () {
    $('.menu-button').toggle(200, 'swing');
  });

  // Stats toggle.
  $('#stats-toggle').click(function () {
    $('#stats').toggle(200, 'swing');
    $('.stats-button').toggle(200, 'swing');
  });

  // People toggle.
  $('#people-toggle').click(function () {
    $('.people-button').toggle(200, 'swing');
  });

  // Tap to close.
  $('.tap-to-close').click(function () {
    console.log('tap to close!');
    $(this).hide(200, 'swing');
  });

  // Don't tap to close.
  $('.landscape-overlay').click(function (e) {
    console.log('landscape overlay!');
    e.stopPropagation();
  });

  jQuery(".fit-box").each(function () {
    var innerWidth = $(this).innerWidth();
    var scrollWidth = $(this)[0].scrollWidth;
    if (scrollWidth > innerWidth) {
      var scale = innerWidth / scrollWidth;
      $(this).css({'-webkit-transform': 'scale(' + scale + ')'});
    }
  });

};
