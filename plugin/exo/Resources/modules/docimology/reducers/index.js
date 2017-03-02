import { combineReducers } from 'redux'

import exerciseReducer from './exercise'
import currentObjectReducer from './current-object'

const docimologyApp = combineReducers({
  exercise: exerciseReducer,
  currentObject: currentObjectReducer
})

export default docimologyApp