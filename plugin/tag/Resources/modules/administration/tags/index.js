import {TagsTool} from '#/plugin/tag/administration/tags/containers/tool'
import {reducer} from '#/plugin/tag/administration/tags/store'

/**
 * Tags administration tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: TagsTool,
  store: reducer,
  initialData: initialData => ({
    tool: {
      name: 'claroline_tag_admin_tool',
      context: initialData.context
    }
  })
})
