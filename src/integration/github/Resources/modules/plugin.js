/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declare applications provided by the GitHub plugin.
 */
registry.add('ClarolineGitHubBundle', {
  oauth: {
    'github': () => { return import(/* webpackChunkName: "authentication-oauth-github" */ '#/integration/github/oauth/github') }
  }
})
