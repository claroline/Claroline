import { createSelector } from 'reselect'
import {selectors as cursusSelectors} from '#/plugin/cursus/tools/trainings/store/selectors'

const STORE_NAME = cursusSelectors.STORE_NAME + '.subscription'
const LIST_NAME = STORE_NAME + '.subscriptions'
const STATISTICS_NAME = STORE_NAME + '.statistics'

const store = (state) => state[cursusSelectors.STORE_NAME].subscription

const statistics = createSelector(
  store,
  (state) => state.statistics
)

const filters = createSelector(
  store,
  (state) => state.subscriptions.filters.reduce((accum, filter) => {
    accum[filter.property] = filter.value
    return accum
  }, {})
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  STATISTICS_NAME,
  statistics,
  filters
}
