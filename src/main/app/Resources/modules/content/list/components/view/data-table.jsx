import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {getType} from '#/main/app/data/types'
import {getPrimaryAction, getActions, isRowSelected} from '#/main/app/content/list/utils'
import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {
  Table,
  TableHeaderCell,
  TableSortingCell,
  TableRow,
  TableCell
} from '#/main/app/content/components/table'
import {DataListView, DataListProperty} from '#/main/app/content/list/prop-types'
import {ListActions, ListPrimaryAction, ListBulkActions} from '#/main/app/content/list/components/actions'
import {toKey} from '#/main/core/scaffolding/text'

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
      data: cellData
    }))
  } else {
    // use render defined in the type definition
    cellRendering = props.definition.render(cellData, props.column.options || {})
  }

  return (
    <TableCell className={`${props.column.type}-cell`}>
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
  definition: T.shape({
    render: T.func,
    components: T.shape({
      table: T.any // todo : find correct typing
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
  />

DataCell.propTypes = {
  rowData: T.object.isRequired,
  action: T.object,
  column: T.shape(
    DataListProperty.propTypes
  ).isRequired
}

const DataTableRow = props => {
  // retrieve the column that should hold the primary action
  let columnAction = props.columns.find(columnDef => columnDef.primary)
  if (!columnAction) {
    // primary column is not displayed, take the first one by default
    columnAction = props.columns[0]
  }

  return (
    <TableRow className={props.selected ? 'selected' : null}>
      {props.onSelect &&
        <TableCell align="center" className="checkbox-cell">
          <input
            type="checkbox"
            checked={props.selected}
            onChange={props.onSelect}
          />
        </TableCell>
      }

      {props.columns.map((column) =>
        <DataCell
          key={column.name}
          column={column}
          rowData={props.row}
          action={props.primaryAction && columnAction === column ? props.primaryAction : undefined}
        />
      )}

      {(!isEmpty(props.actions) || props.actions instanceof Promise) &&
        <TableCell align="right" className="actions-cell">
          <ListActions
            id={`data-table-item-${props.row.id}-actions`}
            actions={props.actions}
          />
        </TableCell>
      }
    </TableRow>
  )
}

DataTableRow.propTypes = {
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

DataTableRow.defaultProps = {
  selected: false
}

const DataTable = props =>
  <Table className="data-table" condensed={'sm' === props.size}>
    <thead>
      <tr>
        {props.selection &&
          <TableHeaderCell align="center" className="checkbox-cell">
            <TooltipOverlay
              id="data-table-select"
              position="right"
              tip={trans(0 < props.selection.current.length ? 'list_deselect_all' : 'list_select_all')}
            >
              <input
                type="checkbox"
                checked={0 < props.selection.current.length}
                onChange={() => {
                  0 === props.selection.current.length ? props.selection.toggleAll(props.data): props.selection.toggleAll([])
                }}
              />
            </TooltipOverlay>
          </TableHeaderCell>
        }

        {props.columns.map(column => 1 < props.count && props.sorting && column.sortable ?
          <TableSortingCell
            key={column.name}
            direction={(column.alias && column.alias === props.sorting.current.property) || column.name === props.sorting.current.property ? props.sorting.current.direction : 0}
            onSort={() => {
              let direction = 1
              if ((column.alias && column.alias === props.sorting.current.property) || column.name === props.sorting.current.property) {
                if (1 === props.sorting.current.direction) {
                  direction = -1
                } else if (-1 === props.sorting.current.direction) {
                  direction = 0
                }
              }

              props.sorting.updateSort(column.alias ? column.alias : column.name, direction)
            }}
          >
            {column.label}
          </TableSortingCell>
          :
          <TableHeaderCell key={column.name}>
            {column.label}
          </TableHeaderCell>
        )}
      </tr>

      {props.selection && 0 < props.selection.current.length &&
        <tr className="selected-actions-row">
          <td colSpan={props.columns.length + (props.selection ? 1:0) + (props.actions ? 1:0) }>
            <ListBulkActions
              count={props.selection.current.length}
              actions={getActions(
                props.selection.current.map(id => props.data.find(row => id === row.id) || {id: id}),
                props.actions
              )}
            />
          </td>
        </tr>
      }
    </thead>

    <tbody>
      {props.data.map((row) =>
        <DataTableRow
          key={row.id}
          row={row}
          columns={props.columns}
          primaryAction={getPrimaryAction(row, props.primaryAction)}
          actions={getActions([row], props.actions)}
          selected={isRowSelected(row, props.selection ? props.selection.current : [])}
          onSelect={
            props.selection ? () => {
              props.selection.toggle(row, !isRowSelected(row, props.selection ? props.selection.current : []))
            }: null
          }
        />
      )}
    </tbody>
  </Table>

DataTable.propTypes    = DataListView.propTypes
DataTable.defaultProps = DataListView.defaultProps

export {
  DataTable
}
