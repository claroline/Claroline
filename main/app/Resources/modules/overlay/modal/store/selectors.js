import {createSelector} from 'reselect'

const STORE_NAME = 'modals'

const modals = state => state[STORE_NAME]

const modal = createSelector(
  [modals],
  (modals) => modals[0]
)

export const selectors = {
  STORE_NAME,
  modal,
  modals
}
