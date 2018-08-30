import {makeReducer} from '#/main/app/store/reducer'

import {reducer as coursesReducer} from '#/plugin/cursus/administration/cursus/course/store/reducer'
import {reducer as cursusReducer} from '#/plugin/cursus/administration/cursus/cursus/store/reducer'
import {reducer as parametersReducer} from '#/plugin/cursus/administration/cursus/parameters/store/reducer'
import {reducer as sessionEventsReducer} from '#/plugin/cursus/administration/cursus/session-event/store/reducer'
import {reducer as sessionsReducer} from '#/plugin/cursus/administration/cursus/session/store/reducer'
import {PARAMETERS_LOAD} from '#/plugin/cursus/administration/cursus/store/actions'

const reducer = {
  parameters: makeReducer({}, {
    [PARAMETERS_LOAD]: (state, action) => action.parameters
  }),
  parametersForm: parametersReducer,
  courses: coursesReducer,
  sessions: sessionsReducer,
  cursus: cursusReducer,
  events: sessionEventsReducer
}

export {
  reducer
}