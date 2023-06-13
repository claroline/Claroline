/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolinePrivacyBundle', {
  account: {
    'privacy': () => {
      return import(/* webpackChunkName: "main-privacy-account-privacy" */ '#/main/privacy/account/privacy')
    }
  },
  administration: {
    'privacy': () => {
      return import(/* webpackChunkName: "main-privacy-admin-privacy" */ '#/main/privacy/administration/privacy')
    }
  }
})
