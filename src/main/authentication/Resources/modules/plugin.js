/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Authentication plugin.
 */
registry.add('ClarolineAuthenticationBundle', {
  /**
   * Provides current user Account sections.
   */
  account: {
    'authentication': () => { return import(/* webpackChunkName: "authentication-account-authentication" */ '#/main/authentication/account/authentication') }
  },

  administration: {
    'authentication': () => { return import(/* webpackChunkName: "authentication-administration-authentication" */ '#/main/authentication/administration/authentication') }
  },

  data: {
    sources: {
      'my_tokens': () => { return import(/* webpackChunkName: "authentication-data-source-my-tokens" */ '#/main/authentication/data/sources/my_tokens') }
    }
  }
})
