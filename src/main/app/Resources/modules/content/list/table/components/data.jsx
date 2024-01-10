import React, {useState} from 'react'

import {DataListView} from '#/main/app/content/list/prop-types'
import {
  getPrimaryAction,
  getActions,
  isRowSelected,
  getDisplayedProps
} from '#/main/app/content/list/utils'

import {Table} from '#/main/app/content/components/table'
import {TableHeader} from '#/main/app/content/list/table/components/header'
import {TableItem} from '#/main/app/content/list/table/components/item'

const TableData = props => {
  const [displayedColumns, setDisplayedColumns] = useState(getDisplayedProps(props.definition).map(column => column.name))

  return (
    <Table className="data-table" condensed={'sm' === props.size}>
      <TableHeader
        count={props.count}
        data={props.data}
        availableColumns={props.definition}
        displayedColumns={displayedColumns}
        changeColumns={setDisplayedColumns}
        selection={props.selection}
        sorting={props.sorting}
        actions={props.actions}
      />

      <tbody>
        {props.data.map((row) =>
          <TableItem
            key={row.id}
            row={row}
            columns={props.definition.filter(prop => -1 !== displayedColumns.indexOf(prop.name))}
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
  )
}

TableData.propTypes    = DataListView.propTypes
TableData.defaultProps = DataListView.defaultProps

export {
  TableData
}
