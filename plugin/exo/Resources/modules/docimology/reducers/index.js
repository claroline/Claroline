import { combineReducers } from 'redux'

import exerciseReducer from './exercise'
import statisticsReducer from './statistics'

const docimologyApp = combineReducers({
  exercise: exerciseReducer,
  statistics: statisticsReducer
})

export default docimologyApp
