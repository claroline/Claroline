import {makeActionCreator} from '#/main/core/utilities/redux'

export const EXERCISE_SET = 'EXERCISE_SET'

export const actions = {}

actions.setExercise = makeActionCreator(EXERCISE_SET, 'exercise')