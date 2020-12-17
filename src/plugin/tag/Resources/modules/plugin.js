/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Tag plugin.
 */
registry.add('ClarolineTagBundle', {
  actions: {
    workspace: {
      'tags': () => { return import(/* webpackChunkName: "tag-action-workspace-tags" */ '#/plugin/tag/workspace/actions/tags') }
    },
    resource: {
      'tags': () => { return import(/* webpackChunkName: "tag-action-resource-tags" */ '#/plugin/tag/resource/actions/tags') }
    }
  },
  data: {
    types: {
      'tag'  : () => { return import(/* webpackChunkName: "plugin-tag-data-tag" */  '#/plugin/tag/data/types/tag') }
    }
  },
  administration: {
    'claroline_tag_admin_tool' : () => { return import(/* webpackChunkName: "plugin-tag-admin-tags" */ '#/plugin/tag/administration/tags') }
  }
})
