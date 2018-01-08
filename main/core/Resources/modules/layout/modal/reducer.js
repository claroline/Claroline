import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  MODAL_SHOW,
  MODAL_FADE,
  MODAL_HIDE
} from './actions'

const reducer = makeReducer({
  type: null,
  props: {},
  fading: false
}, {
  [MODAL_SHOW]: (state, action) => ({
    type: action.modalType,
    props: action.modalProps,
    fading: false
  }),
  [MODAL_FADE]: (state) => ({
    type: state.type,
    props: state.props,
    fading: true
  }),
  [MODAL_HIDE]: () => ({
    type: null,
    props: {},
    fading: false
  })
})

export {
  reducer
}
