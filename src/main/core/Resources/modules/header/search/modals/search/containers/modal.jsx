import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {SearchModal as SearchModalComponent} from '#/main/core/header/search/modals/search/components/modal'
import {actions, reducer, selectors} from '#/main/core/header/search/modals/search/store'

const SearchModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      fetching: selectors.fetching(state),
      empty: selectors.empty(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      search(currentSearch) {
        dispatch(actions.search(currentSearch))
      }
    })
  )(SearchModalComponent)
)

export {
  SearchModal
}
