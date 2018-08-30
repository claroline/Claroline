import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {DropdownButton, MenuItem} from '#/main/core/layout/components/dropdown'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'

import {ListSearch} from '#/main/app/content/list/components/search'
import {constants} from '#/main/app/content/list/constants'
import {DataListProperty} from '#/main/app/content/list/prop-types'

const ListColumnCheck = props =>
  <li className="dropdown-checkbox" role="presentation">
    <label className="checkbox-inline">
      <input
        type="checkbox"
        checked={props.checked}
        disabled={props.disabled}
        onChange={() => props.toggle(props.name)}
      />
      {props.label}
    </label>
  </li>

ListColumnCheck.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  checked: T.bool.isRequired,
  disabled: T.bool.isRequired,
  toggle: T.func.isRequired
}

// TODO : display fixed columns
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
      onSelect={(e) => {
        e.stopPropagation()
        e.preventDefault()
        return false
      }}
    >
      <MenuItem header>{t('list_columns')}</MenuItem>

      {props.available.map(availableColumn =>
        <ListColumnCheck
          key={availableColumn.name}
          name={availableColumn.name}
          label={availableColumn.label}
          checked={-1 !== props.current.indexOf(availableColumn.name)}
          disabled={1 === props.current.length && -1 !== props.current.indexOf(availableColumn.name)}
          toggle={props.toggle}
        />
      )}
    </DropdownButton>
  </TooltipElement>

ListColumnsButton.propTypes = {
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  toggle: T.func.isRequired
}

const ListDisplayButton = props =>
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
      T.shape(DataListProperty.propTypes)
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
    {props.filters && !props.filters.readOnly &&
      <ListSearch
        {...props.filters}
        disabled={props.disabled || props.filters.readOnly}
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
      T.shape(DataListProperty.propTypes)
    ).isRequired,
    toggle: T.func.isRequired
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
