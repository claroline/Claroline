import {combineReducers} from '#/main/app/store/reducer'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'

import {selectors} from '#/plugin/analytics/resource/dashboard/store/selectors'
import {reducer as connectionsReducer}   from '#/plugin/analytics/analytics/resource/connections/store/reducer'
import {reducer as requirementsReducer}   from '#/plugin/analytics/analytics/resource/requirements/store/reducer'

const reducer = combineReducers(makeLogReducer({}, {
  connections: connectionsReducer,
  requirements: requirementsReducer
}, selectors.STORE_NAME+'.'))

export {
  reducer
}
