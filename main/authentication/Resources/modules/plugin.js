/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Authentication plugin.
 */
registry.add('ClarolineAuthenticationBundle', {
  /**
   * Provides Single Sign-On.
   */
  sso: {
    // social networks
    'facebook'    : () => { return import(/* webpackChunkName: "authentication-sso-facebook" */     '#/main/authentication/sso/facebook') },
    'google'      : () => { return import(/* webpackChunkName: "authentication-sso-google" */       '#/main/authentication/sso/google') },
    'linkedin'    : () => { return import(/* webpackChunkName: "authentication-sso-linkedin" */     '#/main/authentication/sso/linkedin') },
    'office_365'  : () => { return import(/* webpackChunkName: "authentication-sso-office_365" */   '#/main/authentication/sso/facebook') },
    'twitter'     : () => { return import(/* webpackChunkName: "authentication-sso-twitter" */      '#/main/authentication/sso/twitter') },
    'windows_live': () => { return import(/* webpackChunkName: "authentication-sso-windows_live" */ '#/main/authentication/sso/windows_live') },
    // generic
    'generic'     : () => { return import(/* webpackChunkName: "authentication-sso-facebook" */     '#/main/authentication/sso/generic') }
  }
})
