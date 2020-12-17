/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('IcapFormulaPluginBundle', {
  tinymcePlugins: {
    'formula': () => { return import(/* webpackChunkName: "plugin-formula-formula-tinymce-plugin" */ '#/plugin/formula/tinymce/plugins/formula') }
  }
})