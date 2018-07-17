import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = {
  isCronConfigured: makeReducer(false, {}),
  picker: makeListReducer('picker'),
  tasks: makeListReducer('tasks'),
  task: makeFormReducer('task', {}, {
    users: makeListReducer('task.users')
  })
}

export {
  reducer
}
