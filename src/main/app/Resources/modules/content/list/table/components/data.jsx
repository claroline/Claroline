import React, {useState} from 'react'
import classes from 'classnames'
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
  if (!props.loaded) {
    data = fill(new Array(15), {id: ''})
  }

  return (
    <Table className={classes('data-table table-striped mb-auto', {
      'table-hover': props.loaded && !props.invalidated
    })} condensed={'sm' === props.size}>
      <TableHeader
        count={props.count}
        data={props.data}
        availableColumns={props.definition}
        displayedColumns={displayedColumns}
        changeColumns={setDisplayedColumns}
        selection={props.selection}
        sorting={props.sorting}
        actions={props.actions}
        disabled={!props.loaded || props.invalidated}
        flush={props.flush}
      />

      <tbody>
        {data.map((row, index) =>
          <TableItem
            key={row.id ? row.id : `row-${index}`}
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
            loaded={props.loaded}
            invalidated={props.invalidated}
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
