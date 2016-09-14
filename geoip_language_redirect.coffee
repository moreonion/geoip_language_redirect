baseUrl = (href) ->
  p = href.indexOf('//')
  p = href.indexOf('/', p+2)
  baseurl = href.substr(0, p + 1)

ready = (fn) ->
  if document.readyState != 'loading'
    fn()
  else
    document.addEventListener('DOMContentLoaded', fn)
  return

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

hideOverlay = ->
  ready(->
    removeEl(document.getElementById('geoip-language-redirect-overlay'))
    if window.geoip_language_redirect_overlay_old_error
      window.onerror = window.geoip_language_redirect_overlay_old_error
    return
  )
  return

removeEl = (el) ->
  el && el.parentNode && el.parentNode.removeChild(el)
  el = null;
  return

checkAndRedirect = ->
  if referrerHasSameHost() or mayBeBot()
    hideOverlay()
    return
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
