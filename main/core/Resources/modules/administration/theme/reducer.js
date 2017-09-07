import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/utilities/redux'

import {
  THEME_UPDATE,
  THEMES_REMOVE
} from './actions'

const reducer = makeReducer([], {
  [THEME_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)

    // retrieve the theme we have updated
    const updatedTheme = newState.find(theme => action.theme.id === theme.id)

    // replace the updated in the themes list
    newState[newState.indexOf(updatedTheme)] = action.theme
  },

  [THEMES_REMOVE]: (state, action) => {
    const newState = cloneDeep(state)

    return newState.filter(theme => -1 !== action.themeIds.indexOf(theme.id))
  }
})

export {
  reducer
}
