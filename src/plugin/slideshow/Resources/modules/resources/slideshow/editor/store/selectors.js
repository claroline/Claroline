import {createSelector} from 'reselect'

import {selectors as resourceSelectors} from '#/main/core/resource/editor'

const slides = createSelector(
  [resourceSelectors.resource],
  (slideshow) => slideshow.slides || []
)

export const selectors = {
  slides
}
