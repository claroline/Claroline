import {combineReducers} from '#/main/app/store/reducer'

import {reducer as catalogReducer} from '#/plugin/cursus/tools/trainings/catalog/store/reducer'
import {reducer as sessionReducer} from '#/plugin/cursus/tools/trainings/session/store/reducer'

const reducer = combineReducers({
  catalog: catalogReducer,
  session: sessionReducer
})

export {
  reducer
}