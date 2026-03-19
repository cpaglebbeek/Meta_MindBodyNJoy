<?php
/**
 * Plugin Name: MindBodyNJoy Tracker
 * Description: Injecteert bezoekersrapport-script op alle front-end pagina's. Mailt rapport naar cglebbeek@gmail.com via tracker.php.
 * Version:     1.0.0
 * Author:      MindBodyNJoy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_footer', 'mbnj_inject_tracker_script', 99 );

function mbnj_inject_tracker_script() {
    ?>
<script>
(function () {
  function sendVisitorReport() {
    try {
      var nav = window.navigator;
      var conn = nav.connection || nav.mozConnection || nav.webkitConnection;
      var perf = window.performance;
      var loadTime = perf && perf.timing && perf.timing.loadEventEnd > 0
        ? perf.timing.loadEventEnd - perf.timing.navigationStart
        : null;

      fetch('https://mindbodynjoy.nl/beheer/tracker.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        keepalive: true,
        body: JSON.stringify({
          url:            window.location.href,
          referrer:       document.referrer,
          timestamp:      new Date().toISOString(),
          language:       nav.language,
          languages:      (nav.languages || []).join(', '),
          platform:       nav.platform,
          cookiesEnabled: nav.cookieEnabled,
          doNotTrack:     nav.doNotTrack,
          screenWidth:    screen.width,
          screenHeight:   screen.height,
          viewportWidth:  window.innerWidth,
          viewportHeight: window.innerHeight,
          colorDepth:     screen.colorDepth,
          pixelRatio:     window.devicePixelRatio,
          timezone:       Intl.DateTimeFormat().resolvedOptions().timeZone,
          timezoneOffset: -(new Date().getTimezoneOffset() / 60),
          connectionType: conn ? (conn.effectiveType || conn.type || 'onbekend') : 'onbekend',
          loadTime:       loadTime
        })
      });
    } catch (e) {}
  }

  if (document.readyState === 'complete') {
    sendVisitorReport();
  } else {
    window.addEventListener('load', sendVisitorReport);
  }
})();
</script>
    <?php
}
