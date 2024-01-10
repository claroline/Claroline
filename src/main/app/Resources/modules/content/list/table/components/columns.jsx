import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {toKey} from '#/main/core/scaffolding/text'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {Menu, MenuHeader} from '#/main/app/overlays/menu'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {DataListProperty} from '#/main/app/content/list/prop-types'

const ColumnsMenu = forwardRef((props, ref) =>
  <ul {...omit(props, 'available', 'current', 'toggle', 'show', 'close')} ref={ref}>
    <MenuHeader>{trans('list_columns')}</MenuHeader>

    {props.available.map(availableColumn =>
      <li key={availableColumn.name} className="dropdown-item" role="presentation">
        <Checkbox
          id={toKey(availableColumn.name)}
          className="mb-0"
          switch={true}
          label={availableColumn.label}
          checked={-1 !== props.current.indexOf(availableColumn.name)}
          disabled={1 === props.current.length && -1 !== props.current.indexOf(availableColumn.name)}
          onChange={() => props.toggle(availableColumn.name)}
        />
      </li>
    )}
  </ul>
)

ColumnsMenu.propTypes = {
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  toggle: T.func.isRequired
}

const TableColumns = props =>
  <Button
    id="list-columns"
    className="btn btn-text-secondary p-0"
    type={MENU_BUTTON}
    icon="fa fa-fw fa-columns"
    label={trans('list_columns_title')}
    tooltip="bottom"
    menu={
      <Menu
        as={ColumnsMenu}
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

TableColumns.propTypes = {
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  current: T.arrayOf(T.string).isRequired,
  change: T.func.isRequired
}

export {
  TableColumns
}
