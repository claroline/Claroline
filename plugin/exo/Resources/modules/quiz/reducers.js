import {VIEW_OVERVIEW, VIEW_EDITOR} from './enums'
import {VIEW_MODE_UPDATE, OPEN_FIRST_STEP} from './actions'

function initialViewMode() {
  return VIEW_OVERVIEW
}

function reduceViewMode(viewMode = initialViewMode(), action = {}) {
  switch (action.type) {
    case VIEW_MODE_UPDATE: {
      return action.mode
    }
    case OPEN_FIRST_STEP: {
      return VIEW_EDITOR
    }
  }
  return viewMode
}

export const reducers = {
  viewMode: reduceViewMode
}
