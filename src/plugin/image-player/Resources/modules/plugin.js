import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineImagePlayerBundle', {
  files: {
    'image/*': () => { return import(/* webpackChunkName: "plugin-image-file-image" */ '#/plugin/image-player/files/image') }
  }
})
