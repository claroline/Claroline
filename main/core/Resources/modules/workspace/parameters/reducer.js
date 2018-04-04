import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {reducer as workspaceReducer} from '#/main/core/workspace/parameters/parameters/reducer'

const reducer = makePageReducer({}, {
  parameters: workspaceReducer,
  resourcesPicker: makeListReducer('resourcesPicker')
})

export {
  reducer
}
