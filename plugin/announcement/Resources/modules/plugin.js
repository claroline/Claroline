/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('announcement', {
  actions: {
    'create-announce' : () => { return import(/* webpackChunkName: "plugin-announcement-action-create-announce" */ '#/plugin/announcement/resources/announcement/actions/create-announce') }
  },

  resources: {
    'claroline_announcement_aggregate': () => { return import(/* webpackChunkName: "plugin-announcement-announcement-resource" */ '#/plugin/announcement/resources/announcement') }
  }
})
