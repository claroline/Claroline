/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Transfer plugin.
 */
registry.add('ClarolineTransferBundle', {
  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'transfer': () => { return import(/* webpackChunkName: "transfer-tool-transfer" */ '#/main/transfer/tools/transfer') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'transfer': () => { return import(/* webpackChunkName: "transfer-tool-transfer" */ '#/main/transfer/tools/transfer') }
  }
})
