/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('website', {
  resources: {
    'icap_website': () => { return import(/* webpackChunkName: "plugin-website-edit-app" */ '#/plugin/website/edit-app/app') }
  }
})
