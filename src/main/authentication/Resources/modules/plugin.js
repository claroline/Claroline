/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Authentication plugin.
 */
registry.add('ClarolineAuthenticationBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  /*header: {
    'authentication': () => { return import(/!* webpackChunkName: "authentication-header-authentication" *!/ '#/main/authentication/header/authentication') }
  },*/

  actions: {
    desktop: {
      'logout': () => { return import(/* webpackChunkName: "authentication-action-logout" */ '#/main/authentication/actions/desktop/logout') }
    }
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'authentication': () => { return import(/* webpackChunkName: "authentication-account-authentication" */ '#/main/authentication/account/authentication') }
  },

  administration: {
    'authentication': () => { return import(/* webpackChunkName: "authentication-administration-authentication" */ '#/main/authentication/administration/authentication') }
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
