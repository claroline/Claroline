import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {AGENDA_UPDATE_FILTER_TYPE, AGENDA_UPDATE_FILTER_WORKSPACE} from '#/plugin/agenda/actions'

const reducer = {
  current: makeFormReducer(
    'events.current',
    {},
    {}
  ),
  filters: combineReducers({
    types: makeReducer(['event', 'task'], {
      [AGENDA_UPDATE_FILTER_TYPE] : (state, action) => action.filters
    }),
    workspaces: makeReducer([], {
      [AGENDA_UPDATE_FILTER_WORKSPACE]: (state, action) => action.filters
    })
  })
}

export {
  reducer
}
