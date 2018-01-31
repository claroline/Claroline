import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = makePageReducer({}, {
  isCronConfigured: makeReducer(false, {}),
  tasks: makeListReducer('tasks'),
  task: makeFormReducer('task')
})

export {
  reducer
}
