import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

const ListEmpty = props =>
  <div className="list-empty">
    <span className={classes('list-empty-icon fa fa-fw mb-3', {
      'fa-ban': !props.hasFilters,
      'fa-search': props.hasFilters
    })} />

    <span className="list-empty-title">
      {trans('list_no_results')}
    </span>

    {props.hasFilters &&
      <p className="list-empty-help m-0">{trans('list_search_no_results')}</p>
    }
  </div>

ListEmpty.propTypes = {
  hasFilters: T.bool
}

ListEmpty.defaultProps = {
  hasFilters: false
}

export {
  ListEmpty
}
