import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = combineReducers({
  picker: makeListReducer('pending.picker'),
  list: makeListReducer('pending.list', {}, {
    invalidated: makeReducer(false, {})
  })
})

export {
  reducer
}
