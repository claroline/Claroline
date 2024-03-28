import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ExampleTool as ExampleToolComponent} from '#/main/example/tools/example/components/tool'
import {reducer, selectors} from '#/main/example//tools/example/store'
import {withReducer} from '#/main/app/store/reducer'

const ExampleTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(ExampleToolComponent)
)
export {
  ExampleTool
}
