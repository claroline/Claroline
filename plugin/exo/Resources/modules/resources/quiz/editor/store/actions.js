import {makeActionCreator} from '#/main/app/store/actions'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store/selectors'
import {validate} from '#/plugin/exo/resources/quiz/editor/validation'

export const QUIZ_STEP_ADD    = 'QUIZ_STEP_ADD'
export const QUIZ_STEP_COPY   = 'QUIZ_STEP_COPY'
export const QUIZ_STEP_MOVE   = 'QUIZ_STEP_MOVE'
export const QUIZ_STEP_REMOVE = 'QUIZ_STEP_REMOVE'

export const QUIZ_ITEM_COPY   = 'QUIZ_ITEM_COPY'
export const QUIZ_ITEM_MOVE   = 'QUIZ_ITEM_MOVE'


export const actions = {}

actions.addStep = makeActionCreator(QUIZ_STEP_ADD, 'step')
actions.copyStep = makeActionCreator(QUIZ_STEP_COPY, 'copy', 'position')
actions.moveStep = makeActionCreator(QUIZ_STEP_MOVE, 'id', 'position')
actions.removeStep = makeActionCreator(QUIZ_STEP_REMOVE, 'id')

actions.copyItem = makeActionCreator(QUIZ_ITEM_COPY, 'item', 'position')
actions.moveItem = makeActionCreator(QUIZ_ITEM_MOVE, 'id', 'position')

actions.save = (quizId) => (dispatch, getState) => {
  validate(
    formSelectors.data(formSelectors.form(getState(), selectors.FORM_NAME))
  ).then(errors => {
    dispatch(formActions.setErrors(selectors.FORM_NAME, errors))
    dispatch(formActions.save(selectors.FORM_NAME, ['exercise_update', {id: quizId}]))
  })
}
