import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineTextPlayerBundle', {
  files: {
    'text/*': () => { return import(/* webpackChunkName: "plugin-text-file-text" */ '#/plugin/text-player/files/text') }
  }
})
