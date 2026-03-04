import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

const pdfPlayer = resourceSelectors.resource

const pdfPlayerScrollMode = createSelector(
  [pdfPlayer],
  (pdfPlayer) => get(pdfPlayer, 'display.scrollMode', 'PAGE')
)

export const selectors = {
  pdfPlayerScrollMode
}
