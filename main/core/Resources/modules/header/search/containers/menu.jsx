import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {SearchMenu as SearchMenuComponent} from '#/main/core/header/search/components/menu'
import {actions, reducer, selectors} from '#/main/core/header/search/store'

const SearchMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      fetching: selectors.fetching(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      search(currentSearch) {
        dispatch(actions.search(currentSearch))
      }
    })
  )(SearchMenuComponent)
)

export {
  SearchMenu
}
