import {makeActionCreator} from './../../utils/actions'

export const EXERCISE_SET = 'EXERCISE_SET'

export const actions = {}

actions.setExercise = makeActionCreator(EXERCISE_SET, 'exercise')