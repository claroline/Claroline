import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/plugin/slideshow/resources/slideshow/store/selectors'

const FORM_NAME = `${baseSelectors.STORE_NAME}.slideshowForm`

const slideshow = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const slides = createSelector(
  [slideshow],
  (slideshow) => slideshow.slides || []
)

export const selectors = {
  FORM_NAME,
  slides
}
