import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/content/search/constants'

const Search = {
  propTypes: {
    disabled: T.bool,
    mode: T.oneOf(
      Object.keys(constants.SEARCH_TYPES)
    ),
    available: T.arrayOf(T.shape({ // todo : use Data propTypes instead
      name: T.string.isRequired,
      options: T.object
    })).isRequired,
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any,
      locked: T.bool
    })).isRequired,
    addFilter: T.func.isRequired,
    removeFilter: T.func.isRequired,
    resetFilters: T.func.isRequired
  },
  defaultProps: {
    disabled: false,
    mode: constants.DEFAULT_SEARCH_TYPE,
    current: []
  }
}

export {
  Search
}
