import {makeReducer} from './../../utils/reducers'

import {
  EXERCISE_SET
} from './../actions/exercise'

function setExercise(exerciseState, action = {}) {
  return action.exercise
}

const exerciseReducer = makeReducer({}, {
  [EXERCISE_SET]: setExercise
})

export default exerciseReducer