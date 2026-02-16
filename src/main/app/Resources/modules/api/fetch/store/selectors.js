import {createSelector} from 'reselect'
import get from 'lodash/get'

const status = createSelector(
  [
    (state) => state,
    (state, storeName) => storeName
  ],
  (state, storeName) => get(state, storeName+'.status', {})
)

const errorCode = createSelector(
  [
    (state) => state,
    (state, storeName) => storeName
  ],
  (state, storeName) => get(state, storeName+'.errorCode', null)
)

const error = createSelector(
  [
    (state) => state,
    (state, storeName) => storeName
  ],
  (state, storeName) => get(state, storeName+'.error', null)
)

const data = createSelector(
  [
    (state) => state,
    (state, storeName) => storeName
  ],
  (state, storeName) => get(state, storeName+'.data', null)
)

const invalidated = createSelector(
  [
    (state) => state,
    (state, storeName) => storeName
  ],
  (state, storeName) => get(state, storeName+'.invalidated', null)
)

export const selectors = {
  status,
  errorCode,
  error,
  data,
  invalidated
}
