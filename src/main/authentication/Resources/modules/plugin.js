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
  },

  actions: {
    user: {
      'password-reset' : () => { return import(/* webpackChunkName: "auth-action-user-password-reset" */  '#/main/authentication/actions/user/password-reset') }
    },
    group: {
      'password-reset': () => { return import(/* webpackChunkName: "auth-action-group-password-reset" */ '#/main/authentication/actions/group/password-reset') }
    }
  },
  sso: {
    'oauth2': () => { return import(/* webpackChunkName: "authentication-sso-oauth2" */ '#/main/authentication/sso/oauth2') }
  },
  oauth: {
    'generic': () => { return import(/* webpackChunkName: "authentication-oauth-generic" */ '#/main/authentication/oauth/generic') }
  }
})
