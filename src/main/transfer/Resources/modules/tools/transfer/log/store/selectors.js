import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/main/transfer/tools/transfer/store/selectors'

const log = createSelector(
  [baseSelectors.store],
  (store) => store.log
)

export const selectors = {
  log
}
