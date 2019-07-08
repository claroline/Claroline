import {makeReducer} from '#/main/app/store/reducer'

import {
  OVERLAY_SHOW,
  OVERLAY_HIDE
} from '#/main/app/overlays/store/actions'

const reducer = makeReducer([], {
  [OVERLAY_SHOW]: (state, action) => {
    if (-1 === state.indexOf(action.overlayId)) {
      const newState = state.slice(0)
      newState.push(action.overlayId)

      return newState
    }

    return state
  },
  [OVERLAY_HIDE]: (state, action) => {
    const newState = state.slice(0)

    const overlayPos = newState.indexOf(action.overlayId)
    if (-1 !== overlayPos) {
      newState.splice(overlayPos, 1)
    }

    return newState
  }
})

export {
  reducer
}
