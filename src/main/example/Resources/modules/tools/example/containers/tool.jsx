import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ExampleTool as ExampleToolComponent} from '#/main/example/tools/example/components/tool'

const ExampleTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(ExampleToolComponent)

export {
  ExampleTool
}
