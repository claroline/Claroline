import {createSelector} from 'reselect'

const STORE_NAME = 'api'

const api = (state) => state[STORE_NAME]

const currentRequests = createSelector(
  [api],
  (api) => api.currentRequests
)

export const selectors = {
  STORE_NAME,
  api,
  currentRequests
}
