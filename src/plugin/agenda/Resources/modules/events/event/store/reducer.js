import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {EVENT_LOAD, EVENT_SET_LOADED} from '#/plugin/agenda/events/event/store/actions'
import {selectors} from '#/plugin/agenda/events/event/store/selectors'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [EVENT_SET_LOADED]: (state, action) => action.loaded
  }),
  event: makeReducer(null, {
    [EVENT_LOAD]: (state, action) => action.event
  }),
  participants: makeListReducer(selectors.LIST_NAME)
})

export {
  reducer
}
