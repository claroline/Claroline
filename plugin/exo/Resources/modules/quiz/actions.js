import invariant from 'invariant'
import {makeActionCreator} from '#/main/core/utilities/redux'
import {navigate} from './router'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
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
      resourceSelect.editable(state) &&
      !select.editorOpened(state) &&
      select.noItems(state)
    ) {
      // Redirects to editor for admins if the quiz is empty
      navigate('editor', false)
      dispatch(openFirstStep(select.firstStepId(state)))
    } else if (mode === VIEW_OVERVIEW &&
      !select.hasOverview(state)
    ) {
      // Redirects to player/test if overview is disabled
      if (!resourceSelect.editable(state)) {
        // User goes to player
        navigate('player', false)
        dispatch(playerActions.play(null, false))
      } else {
        // Admin goes to test mode
        navigate('test', false)
        dispatch(playerActions.play(null, true))
      }
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
