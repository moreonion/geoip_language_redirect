$overlay = null
overlayStyle = null
spinnerStyle = null

baseUrl = (href) ->
  p = href.indexOf('//')
  p = href.indexOf('/', p+2)
  baseurl = href.substr(0, p + 1)

referrerHasSameHost = ->
  if document.referrer
    base = baseUrl(document.referrer)
    return window.location.href.substr(0, base.length) == base
  return false

getLanguageLinks = ->
  links = {}
  for e in document.querySelectorAll('link[rel="alternate"][hreflang]')
    links[e.getAttribute('hreflang')] = e.getAttribute('href')
  return links

mayBeBot = ->
  agent = navigator.userAgent
  if not agent
    return true
  if agent.indexOf('Mozilla') != -1
    iagent = agent.toLowerCase()
    for search in ['bot', 'crawler', 'spider']
      if iagent.indexOf(search) != -1
        return true
    return false
  for engine in ['Webkit', 'Safari', 'Opera', 'Dillo', 'Lynx', 'Links', 'w3m', 'Midori', 'iCab']
    if agent.indexOf(engine) != -1
      return false
  return true

showOverlay = ->
  # hide the body initially to prevent flickering
  overlayStyle = injectStyle('body {visibility: hidden;}')
  # show the body in case anything breaks
  window.onerror = ->
    removeEl(overlayStyle)
    hideOverlay()
    return

  $overlay = jQuery('<div id="geoip-language-redirect-overlay"></div>')

  # append the overlay and show the body once the DOM is loaded
  jQuery( ->
    if ($overlay)
      $overlay.css({
        'position': 'fixed',
        'top': 0,
        'right': 0,
        'bottom': 0,
        'left': 0,
        'z-index': 1000
        })
        .appendTo(document.body)
      setTimeout( ->
        showSpinner()
        return
      , 1000)
    removeEl(overlayStyle)
    return
  )
  return

showSpinner = ->
  if ($overlay)
    if (!(window.hasOwnProperty('geoip_language_redirect') && window.geoip_language_redirect.hasOwnProperty('html') && window.geoip_language_redirect.hasOwnProperty('styles')))
      window.geoip_language_redirect = {
        html: '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>',
        styles: '.spinner{margin:50vh auto 0;width:100px;text-align:center}.spinner > div{width:18px;height:18px;margin-right:10px;background-color:#333;border-radius:100%;display:inline-block;-webkit-animation:sk-bouncedelay 1.4s infinite ease-in-out both;animation:sk-bouncedelay 1.4s infinite ease-in-out both}.spinner .bounce1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.spinner .bounce2{-webkit-animation-delay:-.16s;animation-delay:-.16s}@-webkit-keyframes sk-bouncedelay{0%,80%,100%{-webkit-transform:scale(0)}40%{-webkit-transform:scale(1.0)}}@keyframes sk-bouncedelay{0%,80%,100%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1.0);transform:scale(1.0)}}#geoip-language-redirect-overlay{background:#fff;}'
      }
    $overlay.append(window.geoip_language_redirect.html)
    spinnerStyle = injectStyle(window.geoip_language_redirect.styles)
    return

hideOverlay = ->
  $overlay && $overlay.remove()
  $overlay = null;
  removeEl(overlayStyle)
  removeEl(spinnerStyle)
  return

injectStyle = (css) ->
  head = document.head || document.getElementsByTagName('head')[0]
  style = document.createElement('style')
  style.type = 'text/css'
  if (style.styleSheet)
    style.styleSheet.cssText = css
  else
    style.appendChild(document.createTextNode(css))
  head.appendChild(style)
  return style

removeEl = (el) ->
  el && el.parentNode && el.parentNode.removeChild(el)
  el = null;
  return

checkAndRedirect = ->
  if referrerHasSameHost() or mayBeBot()
    return
  showOverlay()
  links = getLanguageLinks()
  current = document.getElementsByTagName('html')[0].getAttribute('lang')
  jQuery.getJSON('/geoip-language-suggestions').done((data) ->
    for lang in data
      if lang == current
        hideOverlay()
        return
      if links.hasOwnProperty(lang)
        window.location = links[lang]
        return
    hideOverlay()
    return
  ).fail( ->
    hideOverlay()
    return
  )
  return

checkAndRedirect()
