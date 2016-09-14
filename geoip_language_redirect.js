// Generated by CoffeeScript 1.9.3
(function() {
  var baseUrl, checkAndRedirect, getLanguageLinks, hideOverlay, mayBeBot, referrerHasSameHost, removeEl;

  baseUrl = function(href) {
    var baseurl, p;
    p = href.indexOf('//');
    p = href.indexOf('/', p + 2);
    return baseurl = href.substr(0, p + 1);
  };

  referrerHasSameHost = function() {
    var base;
    if (document.referrer) {
      base = baseUrl(document.referrer);
      return window.location.href.substr(0, base.length) === base;
    }
    return false;
  };

  getLanguageLinks = function() {
    var e, i, len, links, ref;
    links = {};
    ref = document.querySelectorAll('link[rel="alternate"][hreflang]');
    for (i = 0, len = ref.length; i < len; i++) {
      e = ref[i];
      links[e.getAttribute('hreflang')] = e.getAttribute('href');
    }
    return links;
  };

  mayBeBot = function() {
    var agent, engine, i, iagent, j, len, len1, ref, ref1, search;
    agent = navigator.userAgent;
    if (!agent) {
      return true;
    }
    if (agent.indexOf('Mozilla') !== -1) {
      iagent = agent.toLowerCase();
      ref = ['bot', 'crawler', 'spider'];
      for (i = 0, len = ref.length; i < len; i++) {
        search = ref[i];
        if (iagent.indexOf(search) !== -1) {
          return true;
        }
      }
      return false;
    }
    ref1 = ['Webkit', 'Safari', 'Opera', 'Dillo', 'Lynx', 'Links', 'w3m', 'Midori', 'iCab'];
    for (j = 0, len1 = ref1.length; j < len1; j++) {
      engine = ref1[j];
      if (agent.indexOf(engine) !== -1) {
        return false;
      }
    }
    return true;
  };

  hideOverlay = function() {
    removeEl(document.getElementById('geoip-language-redirect-overlay'));
  };

  removeEl = function(el) {
    el && el.parentNode && el.parentNode.removeChild(el);
    el = null;
  };

  checkAndRedirect = function() {
    var current, links;
    if (referrerHasSameHost() || mayBeBot()) {
      hideOverlay();
      return;
    }
    links = getLanguageLinks();
    current = document.getElementsByTagName('html')[0].getAttribute('lang');
    jQuery.getJSON('/geoip-language-suggestions').done(function(data) {
      var i, lang, len;
      for (i = 0, len = data.length; i < len; i++) {
        lang = data[i];
        if (lang === current) {
          hideOverlay();
          return;
        }
        if (links.hasOwnProperty(lang)) {
          window.location = links[lang];
          return;
        }
      }
      hideOverlay();
    }).fail(function() {
      hideOverlay();
    });
  };

  checkAndRedirect();

}).call(this);
