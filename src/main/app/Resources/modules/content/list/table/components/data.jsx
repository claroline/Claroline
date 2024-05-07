import React, {useState} from 'react'
import fill from 'lodash/fill'

import {DataListView} from '#/main/app/content/list/prop-types'
import {
  isRowSelected,
  getDisplayedProps
} from '#/main/app/content/list/utils'

import {Table} from '#/main/app/content/components/table'
import {TableHeader} from '#/main/app/content/list/table/components/header'
import {TableItem} from '#/main/app/content/list/table/components/item'

const TableData = props => {
  const [displayedColumns, setDisplayedColumns] = useState(getDisplayedProps(props.definition).map(column => column.name))

  let data = props.data
  if (props.loading) {
    data = fill(new Array(30), {id: ''})
  }

  return (
    <Table className="data-table mb-0" condensed={'sm' === props.size}>
      <TableHeader
        count={props.count}
        data={props.data}
        availableColumns={props.definition}
        displayedColumns={displayedColumns}
        changeColumns={setDisplayedColumns}
        selection={props.selection}
        sorting={props.sorting}
        actions={props.actions}
        disabled={props.loading}
      />

      <tbody>
        {data.map((row, index) =>
          <TableItem
            key={index}
            row={row}
            size={props.size}
            columns={props.definition.filter(prop => -1 !== displayedColumns.indexOf(prop.name))}
            primaryAction={props.primaryAction}
            actions={props.actions}
            selected={isRowSelected(row, props.selection ? props.selection.current : [])}
            onSelect={
              props.selection ? () => {
                props.selection.toggle(row, !isRowSelected(row, props.selection ? props.selection.current : []))
              }: null
            }
            loading={props.loading}
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
