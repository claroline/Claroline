/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Tag plugin.
 */
registry.add('ClarolineTagBundle', {
  data: {
    types: {
      'tag'  : () => { return import(/* webpackChunkName: "plugin-tag-data-tag" */  '#/plugin/tag/data/types/tag') }
    }
  },
  tools: {
    'tags' : () => { return import(/* webpackChunkName: "plugin-tag-tool-tags" */ '#/plugin/tag/tools/tags') }
  }
})
