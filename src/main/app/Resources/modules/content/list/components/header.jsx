import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {Toolbar} from '#/main/app/action/components/toolbar'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {ListDisplay} from '#/main/app/content/list/components/display'
import {Search} from '#/main/app/content/search/components/search'
import {DataListProperty, DataListDisplay} from '#/main/app/content/list/prop-types'

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
        id={props.id + '-toolbar'}
        className="list-toolbar"
        buttonName="list-header-btn btn btn-text-secondary"
        tooltip="bottom"
        actions={props.customActions}
      />
    }

    {props.filters &&
      <Search
        id={props.id + '-search'}
        {...props.filters}
        disabled={props.disabled && isEmpty(props.filters.current)}
      />
    }

    {props.display &&
      <ListDisplay
        {...props.display}
        disabled={props.disabled}
      />
    }
  </div>

ListHeader.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  display: T.shape(
    DataListDisplay.propTypes
  ),

  filters: T.shape({
    mode: T.string.isRequired,
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any
    })).isRequired,
    available: T.arrayOf(
      T.shape(DataListProperty.propTypes)
    ).isRequired,
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
