/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Documentation plugin.
 */
registry.add('ClarolineDocumentationBundle', {
  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    administration: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-administration-documentation" */ '#/plugin/documentation/administration/actions/documentation') }
    },
    desktop: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-desktop-documentation" */ '#/plugin/documentation/desktop/actions/documentation') }
    },
    tool: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-tool-documentation" */ '#/plugin/documentation/tool/actions/documentation') }
    },
    resource: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-resource-documentation" */ '#/plugin/documentation/resource/actions/documentation') }
    },
    workspace: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-workspace-documentation" */ '#/plugin/documentation/workspace/actions/documentation') }
    },
    user: {
      'documentation': () => { return import(/* webpackChunkName: "doc-action-user-documentation" */ '#/plugin/documentation/user/actions/documentation') }
    }
  }
})
