import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {Toolbar} from '#/main/app/action/components/toolbar'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {ListColumns} from '#/main/app/content/list/components/columns'
import {ListDisplay} from '#/main/app/content/list/components/display'
import {Search} from '#/main/app/content/search/components/search'
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
    {!isEmpty(props.customActions) &&
      <Toolbar
        className="list-toolbar"
        buttonName="list-header-btn btn btn-link"
        tooltip="bottom"
        actions={props.customActions}
      />
    }

    {props.filters &&
      <Search
        {...props.filters}
        disabled={props.disabled && isEmpty(props.filters.current)}
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
    mode: T.string.isRequired,
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
  }),

  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

ListHeader.defaultProps = {
  disabled: false
}

export {
  ListHeader
}
