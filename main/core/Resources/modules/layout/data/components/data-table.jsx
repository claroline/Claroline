import React from 'react'
import {PropTypes as T} from 'prop-types'
import {DropdownButton, MenuItem} from 'react-bootstrap'

import {t, transChoice} from '#/main/core/translation'
import {getTypeOrDefault} from '#/main/core/layout/data/index'
import {isPropSortable} from '#/main/core/layout/list/utils'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {
  Table,
  TableHeader,
  TableHeaderCell,
  TableSortingCell,
  TableRow,
  TableCell
} from '#/main/core/layout/table/components/table.jsx'

const isRowSelected = (row, selection) => selection && -1 !== selection.current.indexOf(row.id)

const DataCell = props => {
  const typeDef = getTypeOrDefault(props.column.type)

  return (typeof props.column.renderer === 'function') || !typeDef.components || !typeDef.components.table ?
    <TableCell className={`${props.column.type}-cell`}>
      {typeof props.column.renderer === 'function' ?
        props.column.renderer(props.rowData) : typeDef.render(props.rowData[props.column.name])
      }
    </TableCell>
    :
    React.createElement(typeDef.components.table, {data: props.rowData[props.column.name]})
}

DataCell.propTypes = {
  rowData: T.object.isRequired,
  column: T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired,
    renderer: T.func
  }).isRequired
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
        <TooltipElement
          id={`data-row-${props.index}-actions-tip`}
          tip={t('actions')}
          position="left"
        >
          <DropdownButton
            id={`data-row-${props.index}-actions`}
            title={<span className="fa fa-fw fa-ellipsis-v" />}
            bsStyle="link-default"
            noCaret={true}
            pullRight={true}
          >
            <MenuItem header>Actions</MenuItem>

            {props.actions.filter(action => !action.isDangerous).map((action, actionIndex) => React.createElement(
              MenuItem,
              typeof action.action === 'function' ? {
                key: `data-row-${props.index}-action-${actionIndex}`,
                onClick: () => action.action(props.row)
              } : {
                key: `data-row-${props.index}-action-${actionIndex}`,
                href: action.action
              },
              ([
                <span key={`data-row-${props.index}-action-${actionIndex}-icon`} className={action.icon} />,
                action.label
              ]))
            )}

            {0 !== props.actions.filter(action => action.isDangerous).length &&
              <MenuItem divider />
            }

            {props.actions.filter(action => action.isDangerous).map((action, actionIndex) => React.createElement(
              MenuItem,
              typeof action.action === 'function' ? {
                key: `data-row-${props.index}-action-dangerous-${actionIndex}`,
                className: 'dropdown-link-danger',
                onClick: () => action.action(props.row)
              } : {
                key: `data-row-${props.index}-action-${actionIndex}`,
                className: 'dropdown-link-danger',
                href: action.action
              }, ([
                <span key={`data-row-${props.index}-action-${actionIndex}-dangerous-icon`} className={action.icon} />,
                action.label
              ]))
            )}
          </DropdownButton>
        </TooltipElement>
      </TableCell>
    }
  </TableRow>

DataTableRow.propTypes = {
  index: T.number.isRequired,
  row: T.object.isRequired,
  columns: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired,
    flags: T.number,
    renderer: T.func
  })),
  actions: T.arrayOf(T.shape({
    label: T.string,
    icon: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  })),
  selected: T.bool.isRequired,
  onSelect: T.func.isRequired
}

const DataTable = props =>
  <Table className="data-table">
    <TableHeader>
      {props.selection &&
        <TableHeaderCell align="center" className="checkbox-cell">
          <input
            type="checkbox"
            checked={0 < props.selection.current.length}
            onChange={() => props.selection.toggleAll(props.data.map(row => row.id))}
          />
        </TableHeaderCell>
      }

      {props.columns.map(column => props.sorting && isPropSortable(column) ?
        <TableSortingCell
          key={column.name}
          direction={column.name === props.sorting.current.property ? props.sorting.current.direction : 0}
          onSort={() => props.sorting.updateSort(column.name)}
        >
          {column.label}
        </TableSortingCell>
        :
        <TableHeaderCell key={column.name}>
          {column.label}
        </TableHeaderCell>
      )}
    </TableHeader>

    <tbody>
      {props.data.map((row, rowIndex) =>
        <DataTableRow
          key={`data-row-${rowIndex}`}
          index={rowIndex}
          row={row}
          columns={props.columns}
          actions={props.actions}
          selected={isRowSelected(row, props.selection)}
          onSelect={props.selection ? () => props.selection.toggle(row.id) : null}
        />
      )}
    </tbody>

    <tfoot>
      <tr>
        <td colSpan={props.columns.length + (props.selection ? 1:0) + (props.actions ? 1:0) }>
          {transChoice('list_results_count', props.count, {count: props.count}, 'platform')}
        </td>
      </tr>
    </tfoot>
  </Table>

DataTable.propTypes = {
  data: T.arrayOf(T.object).isRequired,
  count: T.number.isRequired,
  columns: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired,
    flags: T.number,
    renderer: T.func
  })),
  sorting: T.shape({
    current: T.shape({
      property: T.string,
      direction: T.number
    }).isRequired,
    updateSort: T.func.isRequired
  }),
  selection: T.shape({
    current: T.array.isRequired,
    toggle: T.func.isRequired,
    toggleAll: T.func.isRequired
  }),
  actions: T.arrayOf(T.shape({
    label: T.string,
    icon: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  }))
}

DataTable.defaultProps = {
  actions: []
}

export {
  DataTable
}
