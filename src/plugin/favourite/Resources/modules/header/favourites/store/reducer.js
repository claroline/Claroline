import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  FAVOURITE_LOAD,
  FAVOURITE_REMOVE,
  FAVOURITE_SET_LOADED
} from '#/plugin/favourite/header/favourites/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [FAVOURITE_LOAD]: () => true,
    [FAVOURITE_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer({}, {
    [FAVOURITE_LOAD]: (state, action) => action.favourites,
    [FAVOURITE_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = newState[action.type].findIndex(object => object.id = action.object.id)
      if (-1 !== pos) {
        newState[action.type].splice(pos, 1)
      }

      return newState
    }
  })
})
