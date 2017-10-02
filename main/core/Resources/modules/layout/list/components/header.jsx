import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {ListSearch} from '#/main/core/layout/list/components/search.jsx'
import {constants} from '#/main/core/layout/list/constants'
import {DataProperty} from '#/main/core/layout/list/prop-types'

const ListColumnsButton = props =>
  <TooltipElement
    id="list-columns"
    position="bottom"
    tip={t('list_columns_title')}
  >
    <DropdownButton
      id="list-columns-toggle"
      title={<span className={classes('fa fa-fw fa-columns')} />}
      bsStyle="link"
      className="btn-link-default"
      noCaret={true}
      pullRight={true}
      onSelect={(e) => e.stopPropagation()}
    >
      <MenuItem header>{t('list_columns')}</MenuItem>

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

ListColumnsButton.propTypes = {
  available: T.arrayOf(
    T.shape(DataProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  toggle: T.func.isRequired
}

const ListDisplayButton = props => {
  return (
    <TooltipElement
      id="list-format"
      position="bottom"
      tip={t('list_display_modes_title', {current: constants.DISPLAY_MODES[props.current].label})}
    >
      <DropdownButton
        id="list-format-toggle"
        title={<span className={constants.DISPLAY_MODES[props.current].icon} />}
        bsStyle="link"
        className="btn-link-default"
        noCaret={true}
        pullRight={true}
      >
        <MenuItem header>{t('list_display_modes')}</MenuItem>
        {props.available.map(format =>
          <MenuItem
            key={format}
            eventKey={format}
            active={format === props.current}
            onClick={(e) => {
              e.preventDefault()
              props.onChange(format)
            }}
          >
            <span className={constants.DISPLAY_MODES[format].icon} />
            {constants.DISPLAY_MODES[format].label}
          </MenuItem>
        )}
      </DropdownButton>
    </TooltipElement>
  )
}

ListDisplayButton.propTypes = {
  current: T.oneOf(Object.keys(constants.DISPLAY_MODES)).isRequired,
  available: T.arrayOf(
    T.oneOf(Object.keys(constants.DISPLAY_MODES))
  ).isRequired,
  onChange: T.func.isRequired
}

/**
 * Data list configuration.
 *
 * @param props
 * @constructor
 */
const ListActions = props =>
  <div className="list-options">
    {props.columns &&
      <ListColumnsButton {...props.columns} />
    }

    {props.display &&
      <ListDisplayButton {...props.display} />
    }
  </div>

ListActions.propTypes = {
  display: T.shape({
    current: T.oneOf(Object.keys(constants.DISPLAY_MODES)).isRequired,
    available: T.arrayOf(
      T.oneOf(Object.keys(constants.DISPLAY_MODES))
    ).isRequired,
    onChange: T.func.isRequired
  }),
  columns: T.shape({
    current: T.arrayOf(T.string).isRequired,
    available: T.arrayOf(
      T.shape(DataProperty.propTypes)
    ).isRequired,
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
    {props.filters &&
      <ListSearch
        {...props.filters}
        disabled={props.disabled}
      />
    }

    {!props.disabled && (props.columns || props.display) &&
      <ListActions
        display={props.display}
        columns={props.columns}
      />
    }
  </div>

ListHeader.propTypes = {
  disabled: T.bool,
  display: T.shape({
    current: T.oneOf(Object.keys(constants.DISPLAY_MODES)).isRequired,
    available: T.arrayOf(
      T.oneOf(Object.keys(constants.DISPLAY_MODES))
    ).isRequired,
    onChange: T.func.isRequired
  }),

  columns: T.shape({
    current: T.arrayOf(T.string).isRequired,
    available: T.arrayOf(
      T.shape(DataProperty.propTypes)
    ).isRequired,
    toggle: T.func.isRequired
  }),

  filters: T.shape({
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any.isRequired
    })).isRequired,
    available: T.arrayOf(
      T.shape(DataProperty.propTypes)
    ).isRequired,
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
