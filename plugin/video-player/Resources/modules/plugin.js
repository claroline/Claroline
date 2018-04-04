
const VIDEO_PLAYER_PLUGIN = 'video-player'

/**
 * Declares applications provided by the VideoPlayer plugin.
 */
const videoPlayerConfiguration = {
  resources: {
    'video': () => { return import(/* webpackChunkName: "video-player-video-resource" */ '#/plugin/video-player/resources/video') },
    'audio': () => { return import(/* webpackChunkName: "video-player-audio-resource" */ '#/plugin/video-player/resources/audio') }
  }
}

export {
  VIDEO_PLAYER_PLUGIN,
  videoPlayerConfiguration
}
