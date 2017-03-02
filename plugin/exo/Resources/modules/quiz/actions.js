import invariant from 'invariant'
import {makeActionCreator} from './../utils/utils'
import {navigate} from './router'
import select from './selectors'
import {VIEW_OVERVIEW} from './enums'
import {actions as playerActions} from './player/actions'

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
      // Redirects to editor for admins if the quiz is empty
      navigate('editor', false)
      dispatch(openFirstStep(select.firstStepId(state)))
    } else if (mode === VIEW_OVERVIEW &&
      !select.editable(state) &&
      !select.hasOverview(state)
    ) {
      // Redirects to player for users if overview is disabled
      navigate('player', false)
      dispatch(playerActions.play(null, false))
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
