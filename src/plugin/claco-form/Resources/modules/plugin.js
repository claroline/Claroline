/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineClacoFormBundle', {
  actions: {
    claroline_claco_form: {
      'add-entry' : () => { return import(/* webpackChunkName: "plugin-claco-form-action-add-entry" */ '#/plugin/claco-form/resources/claco-form/actions/add-entry') },
      'export-entries' : () => { return import(/* webpackChunkName: "plugin-claco-form-action-add-entry" */ '#/plugin/claco-form/resources/claco-form/actions/export-entries') }
    }
  },

  resources: {
    'claroline_claco_form': () => { return import(/* webpackChunkName: "plugin-claco-form-claco-form-resource" */ '#/plugin/claco-form/resources/claco-form') }
  }
})
