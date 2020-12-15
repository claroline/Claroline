/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineClacoFormBundle', {
  actions: {
    resource: {
      'add-entry' : () => { return import(/* webpackChunkName: "plugin-announcement-action-add-entry" */ '#/plugin/claco-form/resources/claco-form/actions/add-entry') }
    }
  },

  resources: {
    'claroline_claco_form': () => { return import(/* webpackChunkName: "plugin-claco-form-claco-form-resource" */ '#/plugin/claco-form/resources/claco-form') }
  }
})
