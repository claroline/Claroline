import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'footer'

const store = (state) => state[STORE_NAME]

const content = createSelector(
  [store],
  (store) => store.content
)

const display = createSelector(
  [store],
  (store) => store.display
)

const locale = (state) => configSelectors.param(state, 'locale')

export const selectors = {
  STORE_NAME,

  content,
  display,
  locale
}
