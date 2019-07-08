import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {trans} from '#/main/app/intl/translation'

import {
  WALKTHROUGH_START,
  WALKTHROUGH_SKIP,
  WALKTHROUGH_FINISH,
  WALKTHROUGH_NEXT,
  WALKTHROUGH_PREVIOUS,
  WALKTHROUGH_RESTART
} from '#/main/app/overlays/walkthrough/store/actions'

const reducer = combineReducers({
  /**
   * Is the walkthrough currently playing ?
   */
  started: makeReducer(false, {
    [WALKTHROUGH_START]: () => true,
    [WALKTHROUGH_FINISH]: () => false,
    [WALKTHROUGH_RESTART]: () => true
  }),

  /**
   * Did the current user skipped the walkthrough ?
   */
  skipped: makeReducer(false, {
    [WALKTHROUGH_START]: () => false,
    [WALKTHROUGH_SKIP]: () => true,
    [WALKTHROUGH_RESTART]: () => false
  }),

  /**
   * Did the current user finish the walkthrough ?
   */
  finished: makeReducer(false, {
    [WALKTHROUGH_START]: () => false,
    [WALKTHROUGH_FINISH]: () => true,
    [WALKTHROUGH_RESTART]: () => false
  }),

  /**
   * The current playing step.
   */
  current: makeReducer(null, {
    [WALKTHROUGH_START]: () => 0,
    [WALKTHROUGH_NEXT]: (state) => state + 1,
    [WALKTHROUGH_PREVIOUS]: (state) => state - 1,
    [WALKTHROUGH_FINISH]: () => null,
    [WALKTHROUGH_SKIP]: () => null,
    [WALKTHROUGH_RESTART]: () => 0
  }),

  /**
   * The available steps of the walkthrough.
   */
  steps: makeReducer([], {
    [WALKTHROUGH_START]: (state, action) => [].concat(action.steps, [
      // TODO : find a better way to manage last step
      {
        content: {
          icon: 'fa fa-street-view',
          title: trans('end.title', {}, 'walkthrough'),
          message: trans('end.message', {}, 'walkthrough'),
          link: action.documentation
        }
      }
    ])
  }),

  /**
   * Additional walkthroughs related to the current one.
   */
  additional: makeReducer([], {
    [WALKTHROUGH_START]: (state, action) => action.additional
  })
})

export {
  reducer
}
