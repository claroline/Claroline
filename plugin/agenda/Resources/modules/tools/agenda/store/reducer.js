import moment from 'moment'

import {now, getApiFormat} from '#/main/app/intl/date'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {
  AGENDA_CHANGE_VIEW,
  AGENDA_CHANGE_REFERENCE,
  AGENDA_SET_LOADED,
  AGENDA_LOAD_EVENTS
} from '#/plugin/agenda/tools/agenda/store/actions'

const reducer = combineReducers({
  view: makeReducer('month', {
    [AGENDA_CHANGE_VIEW]: (state, action) => action.view
  }),

  referenceDate: makeReducer(now(), {
    [AGENDA_CHANGE_REFERENCE]: (state, action) => moment(action.referenceDate).format(getApiFormat())
  }),

  loaded: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => false,
    [AGENDA_SET_LOADED]: (state, action) => action.loaded,
    [AGENDA_CHANGE_VIEW]: () => false,
    [AGENDA_CHANGE_REFERENCE]: () => false,
    [AGENDA_LOAD_EVENTS]: () => true
  }),

  events: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => [],
    [AGENDA_LOAD_EVENTS]: (state, action) => action.events
  })
})

export {
  reducer
}
