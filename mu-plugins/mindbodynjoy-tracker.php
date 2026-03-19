<?php
/**
 * Plugin Name: MindBodyNJoy Tracker
 * Description: Bezoekersrapport + klik-tracking op alle front-end pagina's. Mailt rapporten naar cglebbeek@gmail.com via tracker.php.
 * Version:     2.0.0
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
  var TRACKER_URL = 'https://mindbodynjoy.nl/beheer/tracker.php';

  function sendReport(payload) {
    try {
      fetch(TRACKER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        keepalive: true,
        body: JSON.stringify(payload)
      });
    } catch (e) {}
  }

  // --- Bezoekersrapport (pageview) ---
  function sendVisitorReport() {
    var nav = window.navigator;
    var conn = nav.connection || nav.mozConnection || nav.webkitConnection;
    var perf = window.performance;
    var loadTime = perf && perf.timing && perf.timing.loadEventEnd > 0
      ? perf.timing.loadEventEnd - perf.timing.navigationStart
      : null;

    sendReport({
      type:           'pageview',
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
    });
  }

  // --- Klik-tracking ---
  function getSection(el) {
    var node = el;
    while (node && node !== document.body) {
      // Elementor sectie
      if (node.dataset && node.dataset.elementType === 'section') {
        var heading = node.querySelector('h1,h2,h3,h4');
        return heading ? heading.textContent.trim().substring(0, 80) : (node.dataset.id || 'sectie');
      }
      // Elementor container
      if (node.dataset && node.dataset.elementType === 'container') {
        var heading = node.querySelector('h1,h2,h3,h4');
        return heading ? heading.textContent.trim().substring(0, 80) : (node.dataset.id || 'container');
      }
      // WordPress widget/block
      if (node.id && node.id !== '') {
        return node.id;
      }
      // <section> of <nav> of <header> of <footer>
      var tag = node.tagName;
      if (tag === 'SECTION' || tag === 'NAV' || tag === 'HEADER' || tag === 'FOOTER') {
        var heading = node.querySelector('h1,h2,h3,h4');
        if (heading) return heading.textContent.trim().substring(0, 80);
        return tag.toLowerCase() + (node.className ? '.' + node.className.split(' ')[0] : '');
      }
      node = node.parentElement;
    }
    return 'onbekend';
  }

  function getElementLabel(el) {
    // Tekst van het element
    var text = (el.textContent || '').trim().substring(0, 100);
    // aria-label als fallback
    if (!text) text = el.getAttribute('aria-label') || '';
    // title als fallback
    if (!text) text = el.getAttribute('title') || '';
    // alt voor images
    if (!text && el.tagName === 'IMG') text = el.getAttribute('alt') || '';
    // Voor inputs
    if (!text && el.tagName === 'INPUT') text = el.value || el.type || '';
    return text || '(geen label)';
  }

  function getElementType(el) {
    var tag = el.tagName.toLowerCase();
    if (tag === 'a') return 'link';
    if (tag === 'button') return 'button';
    if (tag === 'input') return 'input-' + (el.type || 'text');
    if (el.getAttribute('role') === 'button') return 'role-button';
    return tag;
  }

  document.addEventListener('click', function (e) {
    // Zoek het klikbare element (bubble up naar a/button)
    var target = e.target;
    var clickable = target.closest('a, button, input[type="submit"], input[type="button"], [role="button"], .elementor-button');
    if (!clickable) return;

    var section = getSection(clickable);
    var href = clickable.getAttribute('href') || '';

    sendReport({
      type:        'click',
      url:         window.location.href,
      timestamp:   new Date().toISOString(),
      section:     section,
      elementType: getElementType(clickable),
      elementText: getElementLabel(clickable),
      href:        href,
      classes:     clickable.className ? clickable.className.substring(0, 200) : ''
    });
  }, true);

  // --- Init ---
  if (document.readyState === 'complete') {
    sendVisitorReport();
  } else {
    window.addEventListener('load', sendVisitorReport);
  }
})();
</script>
    <?php
}
