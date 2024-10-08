import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Search as SearchComponent} from '#/main/app/content/search/components/search'
import {actions, selectors} from '#/main/app/content/search/store'

// connect search to redux
const Search = connect(
  (state, ownProps) => ({
    currentText: selectors.text(selectors.search(state, ownProps.name)),
    current: selectors.filters(selectors.search(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    updateText(text) {
      dispatch(actions.updateText(ownProps.name, text))
    },
    addFilter(property, value, locked = false) {
      dispatch(actions.addFilter(ownProps.name, property, value, locked))
    },

    removeFilter(filter) {
      dispatch(actions.removeFilter(ownProps.name, filter))
    },

    resetFilters(filters = []) {
      dispatch(actions.resetFilters(ownProps.name, filters))
    }
  })
)(SearchComponent)

Search.propTypes = {
  id: T.string,
  name: T.string.isRequired,
  mode: T.string.isRequired,
  available: T.arrayOf(T.shape({ // todo : use DataProp prop-types
    name: T.string.isRequired,
    options: T.object
  })).isRequired
}

export {
  Search
}
