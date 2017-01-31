import invariant from 'invariant'
import {makeActionCreator} from './../utils/utils'
import {navigate} from './router'
import select from './selectors'
import {VIEW_OVERVIEW} from './enums'

export const VIEW_MODE_UPDATE = 'VIEW_MODE_UPDATE'
export const OPEN_FIRST_STEP = 'OPEN_FIRST_STEP'

export const openFirstStep = makeActionCreator(OPEN_FIRST_STEP, 'stepId')

const updateViewMode = (mode, hasFragment = true) => {
  invariant(mode, 'mode is mandatory')

  return (dispatch, getState) => {
    const state = getState()

    if (
      mode === VIEW_OVERVIEW &&
      !hasFragment &&
      select.editable(state) &&
      !select.editorOpened(state) &&
      select.noItems(state)
    ) {
      navigate('editor', false)
      dispatch(openFirstStep(select.firstStepId(state)))
    } else {
      dispatch({
        type: VIEW_MODE_UPDATE,
        mode
      })
    }
  }
}

export const actions = {
  updateViewMode
}
