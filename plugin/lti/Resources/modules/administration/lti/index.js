import {reducer} from '#/plugin/lti/administration/lti/store'
import {LtiTool} from '#/plugin/lti/administration/lti/containers/tool'

export const App = () => ({
  component: LtiTool,
  store: reducer,
  initialData: (initialData) => Object.assign({
    tool: {
      name: 'lti_tool',
      currentContext: initialData.currentContext
    }
  }, initialData)
})