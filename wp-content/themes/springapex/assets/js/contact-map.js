/**
 * Contact page – interactive global network map (Leaflet + CARTO tiles).
 *
 * Reads the marker list rendered into [data-sa-contact-map] and draws one pin
 * per location with an always-on label and a click popup that links to turn-by
 * turn directions. Degrades to the <noscript> static image when JS/Leaflet is
 * unavailable.
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[ch];
    });
  }

  function tooltipOffset(side, hq) {
    var reach = hq ? 17 : 13;
    switch (side) {
      case 'left':
        return [-reach, 0];
      case 'top':
        return [0, -reach];
      case 'bottom':
        return [0, reach];
      default:
        return [reach, 0];
    }
  }

  function initMap(el) {
    if (el.dataset.saMapReady) {
      return;
    }

    var points;
    try {
      points = JSON.parse(el.getAttribute('data-points') || '[]');
    } catch (err) {
      points = [];
    }
    if (!Array.isArray(points) || !points.length) {
      el.hidden = true;
      return;
    }

    el.dataset.saMapReady = '1';
    var navLabel = el.getAttribute('data-nav-label') || 'Get directions';

    var map = L.map(el, {
      scrollWheelZoom: false, // keep page scroll natural; +/- buttons or ctrl+wheel zoom
      zoomControl: true,
      attributionControl: true,
      minZoom: 1
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      subdomains: 'abcd',
      maxZoom: 19,
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    var latLngs = [];

    points.forEach(function (point) {
      var lat = parseFloat(point.lat);
      var lng = parseFloat(point.lng);
      if (!isFinite(lat) || !isFinite(lng)) {
        return;
      }

      var hq = !!point.hq;
      var side = point.side || 'right';

      var marker = L.marker([lat, lng], {
        title: point.label || '',
        riseOnHover: true,
        icon: L.divIcon({
          className: 'sa-map-pin' + (hq ? ' is-hq' : ''),
          html: '<span class="sa-map-pin__dot"></span>',
          iconSize: hq ? [30, 30] : [22, 22],
          iconAnchor: hq ? [15, 15] : [11, 11]
        })
      }).addTo(map);

      if (point.label) {
        marker.bindTooltip(point.label, {
          permanent: true,
          direction: side,
          className: 'sa-map-label' + (hq ? ' is-hq' : ''),
          offset: tooltipOffset(side, hq)
        });
      }

      var destination = encodeURIComponent(lat + ',' + lng);
      var directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + destination;
      var popupHtml =
        '<div class="sa-map-popup">' +
        (point.label ? '<strong class="sa-map-popup__title">' + escapeHtml(point.label) + '</strong>' : '') +
        (point.address ? '<span class="sa-map-popup__addr">' + escapeHtml(point.address) + '</span>' : '') +
        '<a class="sa-map-popup__nav" href="' + directionsUrl + '" target="_blank" rel="noopener noreferrer">' +
        escapeHtml(navLabel) +
        '</a>' +
        '</div>';

      marker.bindPopup(popupHtml, { className: 'sa-map-popup-wrap' });

      latLngs.push([lat, lng]);
    });

    function fitToPoints() {
      if (latLngs.length === 1) {
        map.setView(latLngs[0], 4);
      } else if (latLngs.length > 1) {
        map.fitBounds(latLngs, { padding: [28, 28], maxZoom: 5 });
      } else {
        map.setView([20, 20], 2);
      }
    }

    fitToPoints();

    // The container may still be settling when the map initialises (reveal
    // animation, late layout, responsive reflow). Re-measure and re-fit once it
    // has, and again whenever its size changes, so the world view stays framed.
    window.setTimeout(function () {
      map.invalidateSize();
      fitToPoints();
    }, 300);

    if (typeof ResizeObserver === 'function') {
      var settleTimer = null;
      var ro = new ResizeObserver(function () {
        window.clearTimeout(settleTimer);
        settleTimer = window.setTimeout(function () {
          map.invalidateSize();
          fitToPoints();
        }, 200);
      });
      ro.observe(el);
    }
  }

  ready(function () {
    var nodes = document.querySelectorAll('[data-sa-contact-map]');
    if (!nodes.length) {
      return;
    }

    var tries = 0;
    (function waitForLeaflet() {
      if (window.L && typeof window.L.map === 'function') {
        Array.prototype.forEach.call(nodes, initMap);
        return;
      }
      if (tries++ > 40) {
        return;
      }
      window.setTimeout(waitForLeaflet, 50);
    })();
  });
})();
