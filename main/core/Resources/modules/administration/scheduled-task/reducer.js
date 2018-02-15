import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {FORM_RESET} from '#/main/core/data/form/actions'

const reducer = makePageReducer({}, {
  isCronConfigured: makeReducer(false, {}),
  picker: makeListReducer('picker'),
  tasks: makeListReducer('tasks'),
  task: makeFormReducer('task', {}, {
    users: makeListReducer('task.users', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/task.users']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
