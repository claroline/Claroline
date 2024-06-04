import {createSelector} from 'reselect'
import get from 'lodash/get'

import {select as listSelectors} from '#/main/app/content/list/store'

const STORE_NAME = 'resourcePicker'
const LIST_NAME = STORE_NAME+'.resources'

const store = (state) => get(state, STORE_NAME)

const current = createSelector(
  [store],
  (state) => state.current
)

const selectedFull = (state) => listSelectors.selectedFull(listSelectors.list(state, LIST_NAME))

export const selectors = {
  STORE_NAME,
  LIST_NAME,

  current,
  selectedFull
}
