import {combineReducers} from '#/main/app/store/reducer'

import {reducer as sessionReducer} from '#/plugin/cursus/tools/trainings/session/store/reducer'
import {reducer as eventReducer} from '#/plugin/cursus/tools/trainings/event/store/reducer'
import {reducer as quotaReducer} from '#/plugin/cursus/tools/trainings/quota/store/reducer'

const reducer = combineReducers({
  session: sessionReducer,
  event: eventReducer,
  quota: quotaReducer
})

export {
  reducer
}