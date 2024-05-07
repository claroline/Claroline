import {createSelector} from 'reselect'

const STORE_NAME = 'text'

const store = (state) => state[STORE_NAME]

const text = createSelector(
  [store],
  (store) => store.resource
)

const availablePlaceholders = createSelector(
  [store],
  (store) => store.availablePlaceholders
)

export const selectors = {
  STORE_NAME,

  text,
  availablePlaceholders
}
