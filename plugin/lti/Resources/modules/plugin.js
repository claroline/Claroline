/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('lti', {
  resources: {
    'ujm_lti_resource': () => { return import(/* webpackChunkName: "plugin-lti-resource" */ '#/plugin/lti/resources/lti') }
  }
})
