import {registry} from '#/main/app/plugins/registry'

registry.add('pdf', {
  files: {
    'application/pdf': () => { return import(/* webpackChunkName: "plugin-pdf-file-pdf" */ '#/plugin/pdf-player/files/pdf') }
  }
})
