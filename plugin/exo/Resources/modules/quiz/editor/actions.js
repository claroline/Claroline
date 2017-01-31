import invariant from 'invariant'
import select from './selectors'
import {makeActionCreator, makeId} from './../../utils/utils'
import {REQUEST_SEND} from './../../api/actions'
import {showModal} from './../../modal/actions'
import {tex} from './../../utils/translate'
import {MODAL_MESSAGE} from './../../modal'
import {denormalize} from './../normalizer'

export const ITEM_CREATE = 'ITEM_CREATE'
export const ITEM_UPDATE = 'ITEM_UPDATE'
export const ITEM_DELETE = 'ITEM_DELETE'
export const ITEM_MOVE = 'ITEM_MOVE'
export const ITEM_HINTS_UPDATE = 'ITEM_HINTS_UPDATE'
export const ITEM_DETAIL_UPDATE = 'ITEM_DETAIL_UPDATE'
export const ITEMS_DELETE = 'ITEMS_DELETE'
export const ITEMS_IMPORT = 'ITEMS_IMPORT'
export const OBJECT_NEXT = 'OBJECT_NEXT'
export const OBJECT_SELECT = 'OBJECT_SELECT'
export const PANEL_QUIZ_SELECT = 'PANEL_QUIZ_SELECT'
export const PANEL_STEP_SELECT = 'PANEL_STEP_SELECT'
export const STEP_CREATE = 'STEP_CREATE'
export const STEP_DELETE = 'STEP_DELETE'
export const STEP_UPDATE = 'STEP_UPDATE'
export const STEP_MOVE = 'STEP_MOVE'
export const QUIZ_UPDATE = 'QUIZ_UPDATE'
export const HINT_ADD = 'HINT_ADD'
export const HINT_CHANGE = 'HINT_CHANGE'
export const HINT_REMOVE = 'HINT_REMOVE'
export const QUIZ_SAVING = 'QUIZ_SAVING'
export const QUIZ_VALIDATING = 'QUIZ_VALIDATING'
export const QUIZ_SAVED = 'QUIZ_SAVED'
export const QUIZ_SAVE_ERROR = 'QUIZ_SAVE_ERROR'

// the following action types lead to quiz data changes that need to be
// properly saved (please maintain this list up-to-date)
export const quizChangeActions = [
  ITEM_CREATE,
  ITEM_DELETE,
  ITEM_UPDATE,
  ITEM_MOVE,
  ITEM_HINTS_UPDATE,
  ITEM_DETAIL_UPDATE,
  ITEMS_IMPORT,
  STEP_CREATE,
  STEP_MOVE,
  STEP_DELETE,
  STEP_UPDATE,
  QUIZ_UPDATE,
  HINT_ADD,
  HINT_CHANGE,
  HINT_REMOVE
]

export const actions = {}

actions.deleteStep = makeActionCreator(STEP_DELETE, 'id')
actions.deleteItem = makeActionCreator(ITEM_DELETE, 'id', 'stepId')
actions.deleteItems = makeActionCreator(ITEMS_DELETE, 'ids')
actions.moveItem = makeActionCreator(ITEM_MOVE, 'id', 'swapId', 'stepId')
actions.moveStep = makeActionCreator(STEP_MOVE, 'id', 'swapId')
actions.nextObject = makeActionCreator(OBJECT_NEXT, 'object')
actions.selectObject = makeActionCreator(OBJECT_SELECT, 'id', 'objectType')
actions.selectQuizPanel = makeActionCreator(PANEL_QUIZ_SELECT, 'panelKey')
actions.selectStepPanel = makeActionCreator(PANEL_STEP_SELECT, 'stepId', 'panelKey')
actions.updateQuiz = makeActionCreator(QUIZ_UPDATE, 'propertyPath', 'value')
actions.updateItem = makeActionCreator(ITEM_UPDATE, 'id', 'propertyPath', 'value')
actions.updateItemDetail = makeActionCreator(ITEM_DETAIL_UPDATE, 'id', 'subAction')
actions.updateItemHints = makeActionCreator(ITEM_HINTS_UPDATE, 'itemId', 'updateType', 'payload')
actions.updateStep = makeActionCreator(STEP_UPDATE, 'id', 'newProperties')
actions.importItems = makeActionCreator(ITEMS_IMPORT, 'stepId', 'items')
actions.quizValidating = makeActionCreator(QUIZ_VALIDATING)
actions.quizSaving = makeActionCreator(QUIZ_SAVING)
actions.quizSaved = makeActionCreator(QUIZ_SAVED)
actions.quizSaveError = makeActionCreator(QUIZ_SAVE_ERROR)

actions.createItem = (stepId, type) => {
  invariant(stepId, 'stepId is mandatory')
  invariant(type, 'type is mandatory')
  return {
    type: ITEM_CREATE,
    id: makeId(),
    stepId,
    itemType: type
  }
}

actions.createStep = () => {
  return {
    type: STEP_CREATE,
    id: makeId()
  }
}

actions.deleteStepAndItems = id => {
  invariant(id, 'id is mandatory')
  return (dispatch, getState) => {
    dispatch(actions.nextObject(select.nextObject(getState())))
    dispatch(actions.deleteItems(getState().steps[id].items.slice()))
    dispatch(actions.deleteStep(id))
  }
}

actions.save = () => {
  return (dispatch, getState) => {
    const state = getState()

    if (!select.valid(state)) {
      dispatch(actions.quizValidating())
      dispatch(showModal(MODAL_MESSAGE, {
        title: tex('editor_invalid_no_save'),
        message: tex('editor_invalid_no_save_desc'),
        bsStyle: 'warning'
      }))
    } else {
      const denormalized = denormalize(state.quiz, state.steps, state.items)
      dispatch({
        [REQUEST_SEND]: {
          route: ['exercise_update', {id: state.quiz.id}],
          request: {
            method: 'PUT' ,
            body: JSON.stringify(denormalized)
          },
          before: () => dispatch(actions.quizSaving()),
          success: () => dispatch(actions.quizSaved()),
          failure: () => dispatch(actions.quizSaveError())
        }
      })
    }
  }
}
