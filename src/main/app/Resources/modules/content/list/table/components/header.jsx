import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

import {ListBulkActions} from '#/main/app/content/list/components/actions'
import {getActions, getDisplayableProps} from '#/main/app/content/list/utils'
import {DataListProperty, DataListSelection, DataListSorting} from '#/main/app/content/list/prop-types'

import {TableHeaderCell, TableSortingCell} from '#/main/app/content/components/table'
import {TableColumns} from '#/main/app/content/list/table/components/columns'

const TableHeader = props => {
  const displayableColumns = getDisplayableProps(props.availableColumns)

  return (
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
                className="form-check-input"
                type="checkbox"
                checked={0 < props.selection.current.length}
                onChange={() => {
                  0 === props.selection.current.length ? props.selection.toggleAll(props.data): props.selection.toggleAll([])
                }}
              />
            </TooltipOverlay>
          </TableHeaderCell>
        }

        {props.availableColumns
          .filter(column => -1 !== props.displayedColumns.indexOf(column.name))
          .map((column, index) => 1 < props.count && props.sorting && column.sortable ?
            <TableSortingCell
              key={column.name}
              className={props.selection && 0 === index ? 'ps-0' : undefined}
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
            <TableHeaderCell key={column.name} className={props.selection && 0 === index ? 'ps-0' : undefined}>
              {column.label}
            </TableHeaderCell>
          )
        }

        {(props.actions || !isEmpty(displayableColumns)) &&
          <TableHeaderCell key="actions" align="right" className="actions-cell">
            {!isEmpty(displayableColumns) &&
              <TableColumns
                current={props.displayedColumns}
                available={displayableColumns}
                change={props.changeColumns}
              />
            }
          </TableHeaderCell>
        }
      </tr>

      {props.selection && 0 < props.selection.current.length &&
        <tr>
          <td className="p-0" colSpan={props.displayedColumns.length + (props.selection ? 1:0) + (props.actions || !isEmpty(displayableColumns) ? 1:0) }>
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
  )
}


TableHeader.propTypes = {
  count: T.number,
  data: T.array,
  availableColumns: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  displayedColumns: T.arrayOf(
    T.string
  ).isRequired,
  sorting: T.shape(
    DataListSorting.propTypes
  ),
  selection: T.shape(
    DataListSelection.propTypes
  ),
  changeColumns: T.func.isRequired,
  actions: T.func
}

export {
  TableHeader
}
