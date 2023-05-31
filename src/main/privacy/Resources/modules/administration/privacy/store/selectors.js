import {createSelector} from 'reselect'

const STORE_NAME = 'privacy'
const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  store
}

