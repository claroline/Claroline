/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Example plugin.
 */
registry.add('ClarolineExampleBundle', {
  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'example': () => { return import(/* webpackChunkName: "core-tool-users" */ '#/main/example/tools/example') }
  }
})
