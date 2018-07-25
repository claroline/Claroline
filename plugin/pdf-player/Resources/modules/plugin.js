import {registry} from '#/main/app/plugins/registry'

registry.add('pdf', {
  resources: {
    'claro_pdf_player': () => { return import(/* webpackChunkName: "plugin-pdf-player-pdf-resource" */ '#/plugin/pdf-player/resources/pdf') }
  }
})