import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const slideshow = createSelector(
  [resource],
  (resource) => resource.slideshow
)

const showOverview = createSelector(
  [slideshow],
  (slideshow) => get(slideshow, 'display.showOverview') || false
)

export const selectors = {
  STORE_NAME,
  slideshow,
  showOverview
}
