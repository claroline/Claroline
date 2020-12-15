import moment from 'moment'

import {now, getApiFormat} from '#/main/app/intl/date'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {
  AGENDA_CHANGE_TYPES,
  AGENDA_CHANGE_VIEW,
  AGENDA_SET_LOADED,
  AGENDA_LOAD_EVENTS,
  AGENDA_LOAD_EVENT
} from '#/plugin/agenda/tools/agenda/store/actions'

const reducer = combineReducers({
  view: makeReducer('month', {
    [AGENDA_CHANGE_VIEW]: (state, action) => action.view
  }),

  referenceDate: makeReducer(now(), {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => now(),
    [AGENDA_CHANGE_VIEW]: (state, action) => moment(action.referenceDate).format(getApiFormat()),
    [AGENDA_LOAD_EVENT]: (state, action) => action.event && action.event.start ? moment(action.event.start).format(getApiFormat()) : state
  }),

  types: makeReducer(['event', 'task'], {
    [AGENDA_CHANGE_TYPES]: (state, action) => action.types
  }),

  loaded: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => false,
    [AGENDA_SET_LOADED]: (state, action) => action.loaded,
    [AGENDA_CHANGE_TYPES]: () => false,
    [AGENDA_CHANGE_VIEW]: () => false,
    [AGENDA_LOAD_EVENTS]: () => true
  }),

  events: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => [],
    [AGENDA_LOAD_EVENTS]: (state, action) => action.events
  }),

  current: makeReducer(null, {
    [AGENDA_LOAD_EVENT]: (state, action) => action.event
  })
})

export {
  reducer
}
