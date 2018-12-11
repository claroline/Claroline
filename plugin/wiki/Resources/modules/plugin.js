/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('wiki', {
  actions: {
    resource: {
      // TODO : finish implementation
      //'add-section' : () => { return import(/* webpackChunkName: "plugin-wiki-action-add-section" */ '#/plugin/wiki/resources/wiki/actions/add-section') }
    }
  },

  resources: {
    'icap_wiki': () => { return import(/* webpackChunkName: "plugin-wiki-wiki-resource" */ '#/plugin/wiki/resources/wiki') }
  }
})