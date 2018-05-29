import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

import {
  RESOURCE_PROPERTY_UPDATE,
  RESOURCE_PARAMS_PROPERTY_UPDATE,
  CATEGORY_ADD,
  CATEGORY_UPDATE,
  CATEGORIES_REMOVE,
  KEYWORD_ADD,
  KEYWORD_UPDATE,
  KEYWORDS_REMOVE
} from '#/plugin/claco-form/resources/claco-form/editor/actions'
import {
  MESSAGE_RESET,
  MESSAGE_UPDATE
} from '#/plugin/claco-form/resources/claco-form/actions'
import {reducer as editorReducer} from '#/plugin/claco-form/resources/claco-form/editor/reducer'
import {reducer as entriesReducer} from '#/plugin/claco-form/resources/claco-form/player/entry/reducer'

const messageReducer = makeReducer({}, {
  [MESSAGE_RESET]: () => {
    return {
      content: null,
      type: null
    }
  },
  [MESSAGE_UPDATE]: (state, action) => {
    return {
      content: action.content,
      type: action.status
    }
  }
})

const clacoFormReducer = makeReducer({}, {
  // replaces clacoForm data after success updates
  [FORM_SUBMIT_SUCCESS+'/clacoFormForm']: (state, action) => action.updatedData,
  [RESOURCE_PROPERTY_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    newState[action.property] = action.value

    return newState
  },
  [RESOURCE_PARAMS_PROPERTY_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    newState['details'][action.property] = action.value

    return newState
  },
  [CATEGORY_ADD]: (state, action) => {
    const newState = cloneDeep(state)
    newState['categories'].push(action.category)

    return newState
  },
  [CATEGORY_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    const index = newState['categories'].findIndex(c => c.id === action.category.id)

    if (index >= 0) {
      newState['categories'][index] = action.category
    }

    return newState
  },
  [CATEGORIES_REMOVE]: (state, action) => {
    const newState = cloneDeep(state)
    action.ids.forEach(id => {
      const index = newState['categories'].findIndex(c => c.id === id)

      if (index >= 0) {
        newState['categories'].splice(index, 1)
      }
    })

    return newState
  },
  [KEYWORD_ADD]: (state, action) => {
    const newState = cloneDeep(state)
    newState['keywords'].push(action.keyword)

    return newState
  },
  [KEYWORD_UPDATE]: (state, action) => {
    const newState = cloneDeep(state)
    const index = newState['keywords'].findIndex(k => k.id === action.keyword.id)

    if (index >= 0) {
      newState['keywords'][index] = action.keyword
    }

    return newState
  },
  [KEYWORDS_REMOVE]: (state, action) => {
    const newState = cloneDeep(state)
    action.ids.forEach(id => {
      const index = newState['keywords'].findIndex(k => k.id === id)

      if (index >= 0) {
        newState['keywords'].splice(index, 1)
      }
    })

    return newState
  }
})

const reducer = {
  clacoForm: clacoFormReducer,
  clacoFormForm: editorReducer,
  canGeneratePdf: makeReducer({}, {}),
  entries: entriesReducer,
  cascadeLevelMax: makeReducer({}, {}),
  message: messageReducer,
  roles: makeReducer({}, {}),
  myRoles: makeReducer({}, {})
}

export {
  reducer
}