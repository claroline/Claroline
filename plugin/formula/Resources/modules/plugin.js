import {registry} from '#/main/app/plugins/registry'

registry.add('formula', {
  tinymcePlugins: {
    'formula': () => { return import(/* webpackChunkName: "plugin-formula-formula-tinymce-plugin" */ '#/plugin/formula/tinymce/plugins/formula') }
  }
})