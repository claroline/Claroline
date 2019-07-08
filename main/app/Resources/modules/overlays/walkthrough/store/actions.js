import invariant from 'invariant'

import {makeActionCreator} from '#/main/app/store/actions'

import {actions as overlayActions} from '#/main/app/overlays/store/actions'

export const WALKTHROUGH_SKIP = 'WALKTHROUGH_SKIP'
export const WALKTHROUGH_START = 'WALKTHROUGH_START'
export const WALKTHROUGH_FINISH = 'WALKTHROUGH_FINISH'
export const WALKTHROUGH_NEXT = 'WALKTHROUGH_NEXT'
export const WALKTHROUGH_PREVIOUS = 'WALKTHROUGH_PREVIOUS'
export const WALKTHROUGH_RESTART = 'WALKTHROUGH_RESTART'

export const actions = {}

actions.play = (steps, additional = [], documentation = null) => {
  invariant(steps, 'steps is required.')

  return {
    type: WALKTHROUGH_START,
    steps,
    additional,
    documentation
  }
}
actions.start = (steps, additional = [], documentation = null) => (dispatch) => {
  dispatch(overlayActions.showOverlay('walkthrough'))
  dispatch(actions.play(steps, additional, documentation))
}

actions.restart = makeActionCreator(WALKTHROUGH_RESTART)
actions.markAsFinished = makeActionCreator(WALKTHROUGH_FINISH)
actions.finish = () => (dispatch) => {
  dispatch(actions.markAsFinished())
  dispatch(overlayActions.hideOverlay('walkthrough'))
}

actions.markAsSkipped = makeActionCreator(WALKTHROUGH_SKIP)
actions.skip = () => (dispatch) => {
  dispatch(actions.markAsSkipped())
  dispatch(overlayActions.hideOverlay('walkthrough'))
}

actions.next = makeActionCreator(WALKTHROUGH_NEXT)
actions.previous = makeActionCreator(WALKTHROUGH_PREVIOUS)
