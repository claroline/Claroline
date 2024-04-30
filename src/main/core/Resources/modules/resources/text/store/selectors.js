import {createSelector} from 'reselect'

const STORE_NAME = 'text'

const resource = (state) => state[STORE_NAME]

const text = createSelector(
  [resource],
  (resource) => resource.text
)

const availablePlaceholders = createSelector(
  [resource],
  (resource) => resource.availablePlaceholders
)

export const selectors = {
  STORE_NAME,

  text,
  availablePlaceholders
}
