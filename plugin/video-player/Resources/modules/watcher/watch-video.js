import observe from './observe'
import $ from 'jquery'

/* global videojs */

observe('video', callback)

function callback (el) {
  const html = $(el).parent().html()
  const parsed = $.parseHTML(html)[0]
  const autoplay = parsed.autoplay ? parsed.autoplay : false
  videojs(el, {
    techOrder: ['html5', 'flash'],
    autoplay: autoplay,
    controls: !autoplay,
    preload: 'metadata'
  }, function () {
    // nothing
  })
}
