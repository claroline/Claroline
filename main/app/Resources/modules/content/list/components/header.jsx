import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ListColumns} from '#/main/app/content/list/components/columns'
import {ListDisplay} from '#/main/app/content/list/components/display'
import {ListSearch} from '#/main/app/content/list/components/search'
import {constants} from '#/main/app/content/list/constants'
import {DataListProperty} from '#/main/app/content/list/prop-types'

/**
 * Data list header.
 *
 * @param props
 * @constructor
 */
const ListHeader = props =>
  <div className="list-header">
    {props.filters &&
      <ListSearch
        {...props.filters}
        disabled={props.disabled}
      />
    }

    {(props.columns || props.display) &&
      <div className="list-options">
        {props.columns &&
          <ListColumns
            {...props.columns}
            disabled={props.disabled}
          />
        }

        {props.display &&
          <ListDisplay
            {...props.display}
            disabled={props.disabled}
          />
        }
      </div>
    }
  </div>

ListHeader.propTypes = {
  disabled: T.bool,
  display: T.shape({
    current: T.oneOf(Object.keys(constants.DISPLAY_MODES)).isRequired,
    available: T.arrayOf(
      T.oneOf(Object.keys(constants.DISPLAY_MODES))
    ).isRequired,
    change: T.func.isRequired
  }),

  columns: T.shape({
    current: T.arrayOf(T.string).isRequired,
    available: T.arrayOf(
      T.shape(DataListProperty.propTypes)
    ).isRequired,
    change: T.func.isRequired
  }),

  filters: T.shape({
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any
    })).isRequired,
    available: T.arrayOf(
      T.shape(DataListProperty.propTypes)
    ).isRequired,
    readOnly: T.bool,
    addFilter: T.func.isRequired,
    removeFilter: T.func.isRequired
  })
}

ListHeader.defaultProps = {
  disabled: false
}

export {
  ListHeader
}
