import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Await} from '#/main/app/components/await'
import {getType} from '#/main/app/data/types'
import {TableRow, TableCell} from '#/main/app/content/components/table'
import {DataListProperty} from '#/main/app/content/list/prop-types'
import {ListPrimaryAction} from '#/main/app/content/list/components/actions'
import {getActions, getPrimaryAction} from '#/main/app/content/list/utils'
import {Toolbar} from '#/main/app/action'

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
      placeholder: props.column.placeholder,
      size: props.size
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
  ).isRequired,
  size: T.oneOf(['sm', 'md'])
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
  ).isRequired,
  size: T.oneOf(['sm', 'md'])
}

const TableItem = props => {
  // retrieve the column that should hold the primary action
  let columnAction = props.columns.find(columnDef => columnDef.primary)
  if (!columnAction) {
    // primary column is not displayed, take the first one by default
    columnAction = props.columns[0]
  }

  let actions
  if (props.loaded && props.actions) {
    actions = getActions([props.row], props.actions)
  }

  return (
    <TableRow className={classes(props.selected && 'table-active', {
      'placeholder-glow': !props.loaded,
      'table-invalidated placeholder-glow': props.loaded && props.invalidated
    })}>
      {props.onSelect &&
        <TableCell align="center" className="checkbox-cell ps-4">
          <input
            className="form-check-input"
            type="checkbox"
            checked={props.selected}
            onChange={props.onSelect}
            disabled={props.loading}
          />
        </TableCell>
      }

      {!props.loaded && props.columns.map((column, index) =>
        <td
          key={column.name}
          className={classes(`${column.type}-cell`,{
            'ps-0': props.onSelect && 0 === index,
            'ps-4': !props.onSelect && 0 === index,
            'primary-cell': column.primary
          })}
        >
          <div className="d-flex flex-direction-row gap-3 align-items-center">
            {column.primary &&
              <div className={classes('placeholder thumbnail thumbnail-icon thumbnail-icon-xs', props.primaryAction && 'bg-primary')} />
            }
            <div className={classes('placeholder rounded-1 w-100', column.primary && props.primaryAction && 'bg-primary')}>
              &nbsp;
            </div>
          </div>
        </td>
      )}

      {props.loaded && props.columns.map((column, index) =>
        <DataCell
          key={column.name}
          className={classes(`${column.type}-cell`,{
            'ps-0': props.onSelect && 0 === index,
            'ps-4': !props.onSelect && 0 === index,
            'primary-cell': column.primary
          })}
          column={column}
          rowData={props.row}
          size={props.size}
          action={props.primaryAction && columnAction === column ? getPrimaryAction(props.row, props.primaryAction)  : undefined}
        />
      )}

      <TableCell align="right" className="actions-cell">
        {props.loaded && actions &&
          <Toolbar
            id={`data-table-item-${props.row.id}-actions`}
            buttonName="btn btn-text-body"
            tooltip="left"
            toolbar="more"
            actions={actions}
            scope="object"
          />
        }

        {!props.loaded && props.actions &&
          <div className="placeholder rounded-1">
            &nbsp;
          </div>
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
  size: T.oneOf(['sm', 'md']),
  primaryAction: T.func,
  actions: T.func,
  selected: T.bool,
  onSelect: T.func,
  loading: T.bool.isRequired,
  loaded: T.bool.isRequired,
  invalidated: T.bool.isRequired
}

TableItem.defaultProps = {
  selected: false
}

export {
  TableItem
}
