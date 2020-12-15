import {createSelector} from 'reselect'
import get from 'lodash/get'

import {select as listSelectors} from '#/main/app/content/list/store'

const STORE_NAME = 'resourceExplorer'

const store = (state) => get(state, STORE_NAME)

const current = createSelector(
  [store],
  (state) => state.current
)

const selectedFull = (state) => listSelectors.selectedFull(listSelectors.list(state, STORE_NAME+'.resources'))

export const selectors = {
  STORE_NAME,

  current,
  selectedFull
}
