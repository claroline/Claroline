import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {ListDisplay} from '#/main/app/content/list/components/display'
import {Search} from '#/main/app/content/search/components/search'
import {DataListProperty, DataListDisplay} from '#/main/app/content/list/prop-types'
import {Button} from '#/main/app/action'

/**
 * Data list header.
 *
 * @param props
 * @constructor
 */
const ListHeader = props =>
  <div className={classes('list-header d-flex align-items-center gap-2 py-2 px-4 bg-body-tertiary', {
    'rounded-3': !props.flush,
    'border-top border-bottom': props.flush,
    'pe-2': !isEmpty(props.addAction),
    'pe-3': isEmpty(props.addAction) && (props.filters || props.display)
  })}>
    {props.filters &&
      <Search
        id={props.id + '-search'}
        {...props.filters}
        autoFocus={props.autoFocus}
        disabled={props.disabled && isEmpty(props.filters.current)}
      />
    }

    {props.display &&
      <ListDisplay
        {...props.display}
        disabled={props.disabled}
      />
    }

    {!isEmpty(props.addAction) &&
      <Button
        id={props.id + '-add'}
        //disabled={props.disabled}
        {...props.addAction}
        className="btn btn-primary"
      />
    }
  </div>

ListHeader.propTypes = {
  id: T.string.isRequired,
  flush: T.bool,
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

  addAction: T.shape(
    ActionTypes.propTypes
  )
}

ListHeader.defaultProps = {
  disabled: false
}

export {
  ListHeader
}
