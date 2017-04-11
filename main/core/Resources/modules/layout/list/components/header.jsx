import React, {PropTypes as T} from 'react'
import classes from 'classnames'

import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {ListSearch} from '#/main/core/layout/list/components/search.jsx'

const ColumnsButton = props =>
  <TooltipElement
    id="list-columns"
    position="top"
    title="Click to display or hide columns"
  >
    <DropdownButton
      id="list-columns-toggle"
      title={
        <span
          className={classes('fa fa-fw fa-columns')}
        />
      }
      bsStyle=""
      className="btn btn-link-default"
      noCaret={true}
      pullRight={true}
      onSelect={(e) => e.stopPropagation()}
    >
      <MenuItem header>Available columns</MenuItem>
      {props.columns.available.map((availableColumn, idx) =>
        <MenuItem eventKey={idx} className="checkbox">
          <label>
            <input type="checkbox" checked={-1 !== props.columns.active.indexOf(availableColumn)}/> {availableColumn}
          </label>
        </MenuItem>
      )}
    </DropdownButton>
  </TooltipElement>

ColumnsButton.propTypes = {
  columns: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.string).isRequired
  }).isRequired
}

const ListFormatButton = props =>
  <TooltipElement
    id="list-format"
    position="top"
    title="List view. (click to change list format)"
  >
    <DropdownButton
      id="list-format-toggle"
      title={
        <span
          className={classes('fa fa-fw', {
            'fa-list': 'list' === props.format,
            'fa-th': 'tiles-sm' === props.format,
            'fa-th-large': 'tiles-lg' === props.format
          })}
        />
      }
      bsStyle=""
      className="btn btn-link-default"
      noCaret={true}
      pullRight={true}
    >
      <MenuItem header>View modes</MenuItem>
      <MenuItem eventKey="1" active={'list' === props.format}>
        <span className="fa fa-fw fa-list" />
        List
      </MenuItem>
      <MenuItem eventKey="2" active={'tiles-sm' === props.format}>
        <span className="fa fa-fw fa-th" />
        Small Tiles
      </MenuItem>
      <MenuItem eventKey="3" active={'tiles-lg' === props.format}>
        <span className="fa fa-fw fa-th-large" />
        Large tiles
      </MenuItem>
    </DropdownButton>
  </TooltipElement>

ListFormatButton.propTypes = {
  format: T.oneOf(['list', 'tiles-sm', 'tiles-lg']).isRequired
}

/**
 * Data list configuration.
 *
 * @param props
 * @constructor
 */
const ListActions = props =>
  <div className="list-actions">
    {props.columns &&
    <ColumnsButton
      columns={props.columns}
    />
    }

    <ListFormatButton
      format={props.format}
    />
  </div>

ListActions.propTypes = {
  format: T.oneOf(['list', 'tiles-sm', 'tiles-lg']).isRequired,
  columns: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.string).isRequired
  })
}

/**
 * Data list header.
 *
 * @param props
 * @constructor
 */
const ListHeader = props =>
  <div className="list-header">
    <ListSearch
      filters={props.filters}
    />

    <ListActions
      format={props.format}
      columns={props.columns}
    />
  </div>

ListHeader.propTypes = {
  format: T.oneOf(['list', 'tiles-sm', 'tiles-lg']).isRequired,
  columns: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.string).isRequired
  }),
  filters: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.string).isRequired,
    onChange: T.func.isRequired
  })
}

export {
  ListHeader
}
