/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('claco-form', {
  resources: {
    'claroline_claco_form': () => { return import(/* webpackChunkName: "plugin-claco-form-claco-form-resource" */ '#/plugin/claco-form/resources/claco-form') }
  }
})
