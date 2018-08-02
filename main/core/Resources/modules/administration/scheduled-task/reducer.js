import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

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
