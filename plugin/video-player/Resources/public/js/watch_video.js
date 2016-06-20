import observe from './observe'

observe('video', callback)

function callback (el) {
  videojs(el, {
    techOrder: ['flash', 'html5'],
    autoplay: false,
    controls: true,
    preload: 'metadata'
  }, function () {
    // nothing
  })
}
