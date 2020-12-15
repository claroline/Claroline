import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/slideshow/resources/slideshow/store/selectors'

const autoPlay = createSelector(
  [baseSelectors.slideshow],
  (slideshow) => slideshow.autoPlay
)

const interval = createSelector(
  [baseSelectors.slideshow],
  (slideshow) => slideshow.interval
)

const display = createSelector(
  [baseSelectors.slideshow],
  (slideshow) => slideshow.display || {}
)

const overviewMessage = createSelector(
  [display],
  (display) => display.description
)

const showControls = createSelector(
  [display],
  (display) => display.showControls
)

const slides = createSelector(
  [baseSelectors.slideshow],
  (slideshow) => slideshow.slides || []
)

export const selectors = {
  overviewMessage,
  slides,
  autoPlay,
  interval,
  showControls
}
