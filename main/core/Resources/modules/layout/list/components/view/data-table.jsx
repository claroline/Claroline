import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {t} from '#/main/core/translation'
import {getTypeOrDefault} from '#/main/core/layout/data/index'
import {getBulkActions, getRowActions, isRowSelected} from '#/main/core/layout/list/utils'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {
  Table,
  TableHeaderCell,
  TableSortingCell,
  TableRow,
  TableCell
} from '#/main/core/layout/table/components/table.jsx'
import {DataAction, DataListView, DataProperty} from '#/main/core/layout/list/prop-types'
import {DataActions, DataBulkActions} from '#/main/core/layout/list/components/data-actions.jsx'

const DataCell = props => {
  const typeDef = getTypeOrDefault(props.column.type)

  return (typeof props.column.renderer === 'function') || !typeDef.components || !typeDef.components.table ?
    <TableCell className={`${props.column.type}-cell`}>
      {typeof props.column.renderer === 'function' ?
        props.column.renderer(props.rowData) : typeDef.render(get(props.rowData, props.column.name))
      }
    </TableCell>
    :
    React.createElement(typeDef.components.table, {data: get(props.rowData, props.column.name)})
}

DataCell.propTypes = {
  rowData: T.object.isRequired,
  column: T.shape(DataProperty.propTypes).isRequired
}

const DataTableRow = props =>
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
      />
    )}

    {0 < props.actions.length &&
      <TableCell align="right" className="actions-cell">
        <DataActions
          id={`data-table-item-${props.index}-actions`}
          item={props.row}
          actions={props.actions}
        />
      </TableCell>
    }
  </TableRow>

DataTableRow.propTypes = {
  index: T.number.isRequired,
  row: T.object.isRequired,
  columns: T.arrayOf(
    T.shape(DataProperty.propTypes)
  ).isRequired,
  actions: T.arrayOf(
    T.shape(DataAction.propTypes)
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
        {1 < props.count && props.selection &&
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
          <td colSpan={props.columns.length + (1 < props.count && props.selection ? 1:0) + (props.actions ? 1:0) }>
            <DataBulkActions
              count={props.selection.current.length}
              selectedItems={props.selection.current.map(id => props.data.find(row => id === row.id))}
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
          actions={getRowActions(props.actions)}
          selected={isRowSelected(row, props.selection ? props.selection.current : [])}
          onSelect={1 < props.count && props.selection ? () => props.selection.toggle(row) : null}
        />
      )}
    </tbody>
  </Table>

DataTable.propTypes    = DataListView.propTypes
DataTable.defaultProps = DataListView.defaultProps

export {
  DataTable
}
