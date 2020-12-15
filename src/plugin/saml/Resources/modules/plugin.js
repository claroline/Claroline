/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Saml plugin.
 */
registry.add('ClarolineSamlBundle', {
  /**
   * Provides Single Sign-On.
   */
  sso: {
    'saml': () => { return import(/* webpackChunkName: "saml-sso-saml" */ '#/plugin/saml/sso/saml') }
  }
})
