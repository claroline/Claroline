import observe from './observe'

observe('video', callback)

function callback (el) {
  videojs(el, {
    techOrder: ['html5', 'flash'],
    autoplay: false,
    controls: true,
    preload: 'metadata'
  }, function () {
    // nothing
  })
}
