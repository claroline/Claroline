import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Await} from '#/main/app/components/await'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'
import {getType} from '#/main/app/data/types'
import {TableRow, TableCell} from '#/main/app/content/components/table'
import {DataListProperty} from '#/main/app/content/list/prop-types'
import {ListActions, ListPrimaryAction} from '#/main/app/content/list/components/actions'

const DataCellContent = props => {
  let cellData
  if (undefined !== props.column.calculated) {
    cellData = typeof props.column.calculated === 'function' ? props.column.calculated(props.rowData) : props.column.calculated
  } else {
    cellData = get(props.rowData, props.column.name)
  }

  let cellRendering
  if (props.column.render) {
    cellRendering = props.column.render(props.rowData)
  } else if (isEmpty(cellData) && props.column.placeholder) {
    cellRendering = props.column.placeholder
  } else if (get(props.definition, 'components.table', null)) {
    // use custom component defined in the type definition
    cellRendering = createElement(props.definition.components.table, merge({}, props.column.options || {}, {
      id: toKey(props.column.name + '-' + props.rowData.id),
      label: props.column.label,
      data: cellData,
      placeholder: props.column.placeholder
    }))
  } else {
    // use render defined in the type definition
    cellRendering = props.definition.render(cellData, props.column.options || {})
  }

  return (
    <TableCell className={classes(props.className, `${props.column.type}-cell`, props.column.primary && 'primary-cell')}>
      {props.action &&
        <ListPrimaryAction
          className="list-primary-action"
          action={props.action}
        >
          {cellRendering || '-'}
        </ListPrimaryAction>
      }

      {!props.action &&
        (cellRendering || '-')
      }
    </TableCell>
  )
}

DataCellContent.propTypes = {
  className: T.string,
  definition: T.shape({
    render: T.func,
    components: T.shape({
      table: T.any
    })
  }).isRequired,
  rowData: T.object.isRequired,
  action: T.object,
  column: T.shape(
    DataListProperty.propTypes
  ).isRequired
}

const DataCell = props =>
  <Await
    for={getType(props.column.type)}
    then={definition => (
      <DataCellContent {...props} definition={definition} />
    )}
    placeholder={
      <td className={props.className}></td>
    }
  />

DataCell.propTypes = {
  className: T.string,
  rowData: T.object.isRequired,
  action: T.object,
  column: T.shape(
    DataListProperty.propTypes
  ).isRequired
}

const TableItem = props => {
  // retrieve the column that should hold the primary action
  let columnAction = props.columns.find(columnDef => columnDef.primary)
  if (!columnAction) {
    // primary column is not displayed, take the first one by default
    columnAction = props.columns[0]
  }

  return (
    <TableRow className={props.selected ? 'selected table-primary' : null}>
      {props.onSelect &&
        <TableCell align="center" className="checkbox-cell">
          <input
            className="form-check-input"
            type="checkbox"
            checked={props.selected}
            onChange={props.onSelect}
          />
        </TableCell>
      }

      {props.columns.map((column, index) =>
        <DataCell
          key={column.name}
          className={props.onSelect && 0 === index ? 'ps-0' : undefined}
          column={column}
          rowData={props.row}
          action={props.primaryAction && columnAction === column ? props.primaryAction : undefined}
        />
      )}

      <TableCell align="right" className="actions-cell">
        {(!isEmpty(props.actions) || props.actions instanceof Promise) &&
          <ListActions
            className="text-end"
            id={`data-table-item-${props.row.id}-actions`}
            actions={props.actions}
          />
        }
      </TableCell>
    </TableRow>
  )
}

TableItem.propTypes = {
  row: T.shape({
    id: T.oneOfType([T.string, T.number]).isRequired
  }).isRequired,
  columns: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  primaryAction: T.object,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  selected: T.bool,
  onSelect: T.func
}

TableItem.defaultProps = {
  selected: false
}

export {
  TableItem
}
