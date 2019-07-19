import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

class SearchMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSearch: null
    }
  }

  updateSearch(searchStr) {
    this.setState({currentSearch: searchStr})

    if (searchStr && 3 <= searchStr.length) {
      this.props.search(searchStr)
    }
  }

  render() {
    return (
      <div className="app-header-search dropdown">
        <input
          type="search"
          className="form-control input-lg"
          placeholder={trans('search', {}, 'actions')}
        />
      </div>
    )
  }
}

SearchMenu.propTypes = {
  fetching: T.bool.isRequired,
  results: T.arrayOf(T.shape({

  })),
  search: T.func.isRequired
}

export {
  SearchMenu
}
