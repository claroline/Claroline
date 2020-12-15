import {combineReducers} from '#/main/app/store/reducer'

import {reducer as sessionReducer} from '#/plugin/cursus/tools/trainings/session/store/reducer'

const reducer = combineReducers({
  session: sessionReducer
})

export {
  reducer
}