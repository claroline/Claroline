import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/analytics/charts/latest-actions/store'
import {LatestActionsChart as LatestActionsChartComponent} from '#/plugin/analytics/charts/latest-actions/components/chart'

const LatestActionsChart = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: listSelectors.loaded(listSelectors.list(state, selectors.STORE_NAME)),
      filters: listSelectors.filters(listSelectors.list(state, selectors.STORE_NAME)),
      data: listSelectors.data(listSelectors.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      fetchActions(url) {
        dispatch(listActions.fetchData(selectors.STORE_NAME, url))
      },
      changeFilter(filter = null, url) {
        if (filter) {
          dispatch(listActions.addFilter(selectors.STORE_NAME, 'action', filter))
        } else {
          dispatch(listActions.removeFilter(selectors.STORE_NAME, 'action'))
        }

        dispatch(listActions.fetchData(selectors.STORE_NAME, url))
      }
    })
  )(LatestActionsChartComponent)
)

export {
  LatestActionsChart
}
