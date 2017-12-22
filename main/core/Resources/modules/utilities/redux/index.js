/**
 * Claroline Redux.
 * Provides redux integration inside the claroline project.
 */

export {
  makeActionCreator,
  makeInstanceActionCreator
} from '#/main/core/utilities/redux/actions'

export {
  combineReducers, // reexported from redux
  makeReducer,
  makeInstanceReducer,
  reduceReducers
} from '#/main/core/utilities/redux/reducer'

export {
  createStore
} from '#/main/core/utilities/redux/store'
