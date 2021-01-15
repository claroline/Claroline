import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  embedded: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'shortcut')]: (state, action) => action.resourceData.embedded || state,
  })
})

export {
  reducer
}
