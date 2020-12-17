import {reducer} from '#/plugin/tag/administration/tags/store'
import {TagsTool} from '#/plugin/tag/administration/tags/containers/tool'

/**
 * Tags administration tool application.
 */
export default {
  component: TagsTool,
  store: reducer
}
