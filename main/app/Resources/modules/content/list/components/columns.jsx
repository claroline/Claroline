import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {MenuItem} from '#/main/app/overlays/menu'

import {DataListProperty} from '#/main/app/content/list/prop-types'

const ColumnsMenu = props =>
  <ul className="dropdown-menu dropdown-menu-right">
    <MenuItem header={true}>{trans('list_columns')}</MenuItem>

    {props.available.map(availableColumn =>
      <li key={availableColumn.name} className="dropdown-checkbox" role="presentation">
        <label className="checkbox-inline">
          <input
            type="checkbox"
            checked={-1 !== props.current.indexOf(availableColumn.name)}
            disabled={1 === props.current.length && -1 !== props.current.indexOf(availableColumn.name)}
            onChange={() => props.toggle(availableColumn.name)}
          />
          {availableColumn.label}
        </label>
      </li>
    )}
  </ul>

ColumnsMenu.propTypes = {
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  toggle: T.func.isRequired
}

// TODO : display fixed columns
const ListColumns = props =>
  <Button
    id="list-columns"
    className="list-header-btn btn btn-link"
    type={MENU_BUTTON}
    icon="fa fa-fw fa-columns"
    label={trans('list_columns_title')}
    tooltip="bottom"
    disabled={props.disabled}
    menu={
      <ColumnsMenu
        current={props.current}
        available={props.available}
        toggle={(column) => {
          const newColumns = props.current.slice(0)

          // checks if the column is displayed
          const pos = newColumns.indexOf(column)
          if (-1 === pos) {
            // column is not displayed, display it
            newColumns.push(column)
          } else {
            // column is displayed, hide it
            newColumns.splice(pos, 1)
          }

          // updates displayed column list
          props.change(newColumns)
        }}
      />
    }
  />

ListColumns.propTypes = {
  disabled: T.bool,
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  change: T.func.isRequired
}

export {
  ListColumns
}
