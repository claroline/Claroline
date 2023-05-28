import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

const ListEmpty = props =>
  <div className="list-empty">
    <div className="list-empty-info">
      <span className="list-empty-icon fa fa-refresh" />

      <div className="list-empty-content">
        {trans(props.hasFilters ? 'list_search_no_results' : 'list_no_results')}

        {props.contentDesc &&
          <p className="list-content-desc">
            {props.contentDesc}
          </p>
        }
      </div>
    </div>
  </div>

ListEmpty.propTypes = {
  contentDesc: T.string,
  hasFilters: T.bool
}

ListEmpty.defaultProps = {
  hasFilters: false
}

export {
  ListEmpty
}
