import {createSelector} from 'reselect'

const STORE_NAME = 'claro_slideshow'

const slideshow = (state) => state[STORE_NAME]

const slides = createSelector(
  [slideshow],
  (slideshow) => slideshow.slides || []
)

const autoPlay = createSelector(
  [slideshow],
  (slideshow) => slideshow.autoPlay
)

const interval = createSelector(
  [slideshow],
  (slideshow) => slideshow.interval
)

const display = createSelector(
  [slideshow],
  (slideshow) => slideshow.display || {}
)

const showControls = createSelector(
  [display],
  (display) => display.showControls
)


export const selectors = {
  STORE_NAME,

  slideshow,
  slides,
  autoPlay,
  interval,
  showControls
}
