import {makeReducer} from '#/main/core/utilities/redux'
import {update} from './../../utils/utils'

import {
  SELECT_TOGGLE
} from './../actions/select'

function toggleSelect(state, actions) {
  const itemPos = state.indexOf(actions.itemId)
  if (-1 === itemPos) {
    // Item not selected
    return update(state, {$push: [actions.itemId]})
  } else {
    // Item selected
    return update(state, {$splice: [[itemPos, 1]]})
  }
}

const selectReducer = makeReducer([], {
  [SELECT_TOGGLE]: toggleSelect
})

export default selectReducer
