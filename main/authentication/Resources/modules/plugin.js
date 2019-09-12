/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Authentication plugin.
 */
registry.add('ClarolineAuthenticationBundle', {
  integration: {
    'tokens' : () => { return import(/* webpackChunkName: "authentication-integration-tokens" */ '#/main/authentication/integration/tokens')}
  },

  /**
   * Provides Single Sign-On.
   */
  sso: {
    // social networks
    'dropbox'     : () => { return import(/* webpackChunkName: "authentication-sso-dropbox" */      '#/main/authentication/sso/dropbox') },
    'facebook'    : () => { return import(/* webpackChunkName: "authentication-sso-facebook" */     '#/main/authentication/sso/facebook') },
    'github'      : () => { return import(/* webpackChunkName: "authentication-sso-github" */       '#/main/authentication/sso/github') },
    'google'      : () => { return import(/* webpackChunkName: "authentication-sso-google" */       '#/main/authentication/sso/google') },
    'linkedin'    : () => { return import(/* webpackChunkName: "authentication-sso-linkedin" */     '#/main/authentication/sso/linkedin') },
    'office_365'  : () => { return import(/* webpackChunkName: "authentication-sso-office_365" */   '#/main/authentication/sso/office_365') },
    'twitter'     : () => { return import(/* webpackChunkName: "authentication-sso-twitter" */      '#/main/authentication/sso/twitter') },
    'windows_live': () => { return import(/* webpackChunkName: "authentication-sso-windows_live" */ '#/main/authentication/sso/windows_live') },
    // generic
    'generic'     : () => { return import(/* webpackChunkName: "authentication-sso-facebook" */     '#/main/authentication/sso/generic') }
  }
})
