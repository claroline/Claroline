import {combineReducers} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = combineReducers({
  groups: makeListReducer('modals.groups', {}),
  users: makeListReducer('modals.users', {})
})

export {
  reducer
}
