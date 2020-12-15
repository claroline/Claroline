import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/main/core/administration/parameters/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME + '.icons'

const icons = (state) => state[STORE_NAME]

const mimeTypes = createSelector(
  [icons],
  (icons) => icons.mimeTypes
)

export const selectors = {
  STORE_NAME,

  mimeTypes
}
