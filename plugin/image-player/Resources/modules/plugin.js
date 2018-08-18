import {registry} from '#/main/app/plugins/registry'

registry.add('image', {
  files: {
    'image/*': () => { return import(/* webpackChunkName: "plugin-image-file-image" */ '#/plugin/image-player/files/image') }
  }
})
