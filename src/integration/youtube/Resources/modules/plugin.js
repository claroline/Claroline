import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineYouTubeBundle', {

  resources: {
    'youtube_video': () => { return import(/* webpackChunkName: "youtube-resource-video" */ '#/integration/youtube/resources/video') }
  }
})
