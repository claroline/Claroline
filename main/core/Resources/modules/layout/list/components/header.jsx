import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {ListSearch} from '#/main/core/layout/list/components/search.jsx'
import {getListDisplay} from '#/main/core/layout/list/utils'
import {LIST_DISPLAY_LIST_VALUE} from '#/main/core/layout/list/default'

const ColumnsButton = props =>
  <TooltipElement
    id="list-columns"
    position="top"
    tip="Click to display or hide columns"
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
      {props.available.map(availableColumn =>
        <li key={availableColumn.name} className="dropdown-checkbox" role="presentation">
          <label className="checkbox-inline">
            <input
              type="checkbox"
              checked={-1 !== props.current.indexOf(availableColumn.name)}
              onChange={() => props.toggle(availableColumn.name)}
            />
            {availableColumn.label}
          </label>
        </li>
      )}
    </DropdownButton>
  </TooltipElement>

ColumnsButton.propTypes = {
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    label: T.string.isRequired
  })).isRequired,
  current: T.arrayOf(T.string).isRequired,
  toggle: T.func.isRequired
}

const ListDisplayButton = props => {
  const currentFormat = getListDisplay(props.available, props.current)

  return (
    <TooltipElement
      id="list-format"
      position="top"
      tip="List view. (click to change list format)"
    >
      <DropdownButton
        id="list-format-toggle"
        title={currentFormat[2] ?
          <span className={currentFormat[2]} /> : currentFormat[1]
        }
        bsStyle=""
        className="btn btn-link-default"
        noCaret={true}
        pullRight={true}
      >
        <MenuItem header>{t('list_formats')}</MenuItem>
        {props.available.map(format =>
          <MenuItem
            key={format[0]}
            eventKey={format[0]}
            active={format[0] === props.current}
            onClick={() => props.onChange(format[0])}
          >
            {format[2] && <span className={format[2]} />}
            {format[1]}
          </MenuItem>
        )}
      </DropdownButton>
    </TooltipElement>
  )
}

ListDisplayButton.propTypes = {
  available: T.array.isRequired,
  current: T.string.isRequired,
  onChange: T.func.isRequired
}

/**
 * Data list configuration.
 *
 * @param props
 * @constructor
 */
const ListActions = props =>
  <div className="list-actions">
    {(props.columns && LIST_DISPLAY_LIST_VALUE === props.display.current) &&
      <ColumnsButton
        {...props.columns}
      />
    }

    {props.display && 1 < props.display.available.length &&
      <ListDisplayButton
        {...props.display}
      />
    }
  </div>

ListActions.propTypes = {
  display: T.shape({
    current: T.string.isRequired,
    available: T.array.isRequired,
    onChange: T.func.isRequired
  }),
  columns: T.shape({
    current: T.arrayOf(T.string).isRequired,
    available: T.arrayOf(T.object).isRequired,
    toggle: T.func.isRequired
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
      {...props.filters}
    />

    <ListActions
      display={props.display}
      columns={props.columns}
    />
  </div>

ListHeader.propTypes = {
  display: T.shape({
    current: T.string.isRequired,
    available: T.array.isRequired,
    onChange: T.func.isRequired
  }),
  columns: T.shape({
    current: T.arrayOf(T.string).isRequired,
    available: T.arrayOf(T.object).isRequired,
    toggle: T.func.isRequired
  }),
  filters: T.shape({
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any.isRequired
    })).isRequired,
    available: T.arrayOf(T.object).isRequired,
    addFilter: T.func.isRequired,
    removeFilter: T.func.isRequired
  })
}

export {
  ListHeader
}
