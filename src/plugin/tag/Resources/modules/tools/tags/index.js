import {reducer} from '#/plugin/tag/tools/tags/store'
import {TagsTool} from '#/plugin/tag/tools/tags/containers/tool'
import {TagsMenu} from '#/plugin/tag/tools/tags/components/menu'

/**
 * Tags tool application.
 */
export default {
  component: TagsTool,
  menu: TagsMenu,
  store: reducer
}
