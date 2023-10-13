/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Authentication plugin.
 */
registry.add('ClarolineAuthenticationBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'authentication': () => { return import(/* webpackChunkName: "authentication-header-authentication" */ '#/main/authentication/header/authentication') }
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'tokens': () => { return import(/* webpackChunkName: "authentication-account-tokens" */ '#/main/authentication/account/tokens') }
  },

  integration: {
    'tokens': () => { return import(/* webpackChunkName: "authentication-integration-tokens" */ '#/main/authentication/integration/tokens')},
    'ips'   : () => { return import(/* webpackChunkName: "authentication-integration-ips" */    '#/main/authentication/integration/ips')}
  },

  data: {
    sources: {
      'my_tokens': () => { return import(/* webpackChunkName: "authentication-data-source-my-tokens" */ '#/main/authentication/data/sources/my_tokens') },
    }
  }
})
