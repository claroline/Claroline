import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {PARAMETERS_LOAD} from '#/plugin/cursus/administration/cursus/store/actions'
import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'
import {reducer as coursesReducer} from '#/plugin/cursus/administration/cursus/course/store/reducer'
import {reducer as cursusReducer} from '#/plugin/cursus/administration/cursus/cursus/store/reducer'
import {reducer as queuesReducer} from '#/plugin/cursus/administration/cursus/queue/store/reducer'
import {reducer as sessionEventsReducer} from '#/plugin/cursus/administration/cursus/session-event/store/reducer'
import {reducer as sessionsReducer} from '#/plugin/cursus/administration/cursus/session/store/reducer'

const reducer = combineReducers({
  parameters: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters || {},
    [PARAMETERS_LOAD]: (state, action) => action.parameters
  }),
  parametersForm: makeFormReducer(selectors.STORE_NAME + '.parametersForm'),
  courses: coursesReducer,
  sessions: sessionsReducer,
  cursus: cursusReducer,
  events: sessionEventsReducer,
  queues: queuesReducer
})

export {
  reducer
}