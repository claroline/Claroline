import {makeReducer} from '#/main/core/utilities/redux'

import {
  PAGE_FULLSCREEN_TOGGLE
} from './actions'

function toggleFullscreen(currentState) {
  return Object.assign({}, currentState, {
    fullscreen: !currentState.fullscreen
  })
}

const reducer = makeReducer({
  fullscreen: false
}, {
  [PAGE_FULLSCREEN_TOGGLE]: toggleFullscreen
})

export {reducer}
