import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/app/store/reducer'

import {
  SUBTITLE_ADD,
  SUBTITLE_UPDATE,
  SUBTITLE_REMOVE
} from '#/plugin/video-player/resources/video/editor/actions'

const reducer = {
  tracks: makeReducer([], {
    [SUBTITLE_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState.push(action.subtitle)

      return newState
    },
    [SUBTITLE_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState.findIndex(s => s.id === action.subtitle.id)

      if (index > -1) {
        newState[index] = action.subtitle
      }

      return newState
    },
    [SUBTITLE_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState.findIndex(s => s.id === action.id)

      if (index > -1) {
        newState.splice(index, 1)
      }

      return newState
    }
  })
}

export {
  reducer
}