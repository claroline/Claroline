import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {t} from '#/main/core/translation'
import {getTypeOrDefault} from '#/main/core/data/index'
import {getBulkActions, getRowActions, isRowSelected} from '#/main/core/data/list/utils'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {
  Table,
  TableHeaderCell,
  TableSortingCell,
  TableRow,
  TableCell
} from '#/main/core/layout/table/components/table.jsx'
import {DataListAction, DataListView, DataListProperty} from '#/main/core/data/list/prop-types'
import {ListActions, ListPrimaryAction, ListBulkActions} from '#/main/core/data/list/components/actions.jsx'

const DataCell = props => {
  const typeDef = getTypeOrDefault(props.column.type)

  let cellData
  if (undefined !== props.column.calculated) {
    cellData = props.column.calculated(props.rowData)
  } else {
    cellData = get(props.rowData, props.column.name)
  }

  let cellRendering
  if (props.column.renderer) {
    cellRendering = props.column.renderer(props.rowData)
  } else if (typeDef.components && typeDef.components.table) {
    // use custom component defined in the type definition
    cellRendering = React.createElement(typeDef.components.table, merge({data: cellData}, props.column.options || {}))
  } else {
    // use render defined in the type definition
    cellRendering = typeDef.render(cellData, props.column.options || {})
  }

  return (
    <TableCell className={`${props.column.type}-cell`}>
      <ListPrimaryAction
        item={props.rowData}
        action={props.action}
      >
        {cellRendering || '-'}
      </ListPrimaryAction>
    </TableCell>
  )
}

DataCell.propTypes = {
  action: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),
  rowData: T.object.isRequired,
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

      {props.columns.map((column, columnIndex) =>
        <DataCell
          key={`data-cell-${columnIndex}`}
          column={column}
          rowData={props.row}
          action={props.primaryAction && columnAction === column ? props.primaryAction : undefined}
        />
      )}

      {0 < props.actions.length &&
        <TableCell align="right" className="actions-cell">
          <ListActions
            id={`data-table-item-${props.index}-actions`}
            item={props.row}
            actions={props.actions}
          />
        </TableCell>
      }
    </TableRow>
  )
}

DataTableRow.propTypes = {
  index: T.number.isRequired,
  row: T.object.isRequired,
  columns: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  primaryAction: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),
  actions: T.arrayOf(
    T.shape(DataListAction.propTypes)
  ),
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
            <TooltipElement
              id="data-table-select"
              position="right"
              tip={t(0 < props.selection.current.length ? 'list_deselect_all' : 'list_select_all')}
            >
              <input
                type="checkbox"
                checked={0 < props.selection.current.length}
                onChange={() => props.selection.toggleAll(props.data)}
              />
            </TooltipElement>
          </TableHeaderCell>
        }

        {props.columns.map(column => 1 < props.count && props.sorting && column.sortable ?
          <TableSortingCell
            key={column.name}
            direction={(column.alias && column.alias === props.sorting.current.property) || column.name === props.sorting.current.property ? props.sorting.current.direction : 0}
            onSort={() => props.sorting.updateSort(column.alias ? column.alias : column.name)}
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
              selectedItems={props.selection.current.map(id => props.data.find(row => id === row.id) || {id: id})}
              actions={getBulkActions(props.actions)}
            />
          </td>
        </tr>
      }
    </thead>

    <tbody>
      {props.data.map((row, rowIndex) =>
        <DataTableRow
          key={`data-row-${rowIndex}`}
          index={rowIndex}
          row={row}
          columns={props.columns}
          primaryAction={props.primaryAction}
          actions={getRowActions(props.actions)}
          selected={isRowSelected(row, props.selection ? props.selection.current : [])}
          onSelect={props.selection ? () => props.selection.toggle(row) : null}
        />
      )}
    </tbody>
  </Table>

DataTable.propTypes    = DataListView.propTypes
DataTable.defaultProps = DataListView.defaultProps

export {
  DataTable
}
