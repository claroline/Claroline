import {combineReducers} from '#/main/app/store/reducer'

import {reducer as frameworksReducer} from '#/plugin/competency/administration/competency/framework/store/reducer'
import {reducer as scalesReducer} from '#/plugin/competency/administration/competency/scale/store/reducer'

const reducer = combineReducers({
  frameworks: frameworksReducer,
  scales: scalesReducer
})

export {
  reducer
}