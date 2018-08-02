import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DropdownButton, MenuItem} from '#/main/core/layout/components/dropdown'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'

import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'
import {DataListProperty, DataListView} from '#/main/app/content/list/prop-types'
import {
  getPrimaryAction,
  getActions,
  getPropDefinition,
  getSortableProps,
  isRowSelected
} from '#/main/app/content/list/utils'
import {ListBulkActions} from '#/main/app/content/list/components/actions'

const DataGridItem = props =>
  <li className="data-grid-item-container">
    {React.createElement(props.card, {
      className: classes({selected: props.selected}),
      size: props.size,
      orientation: props.orientation,
      data: props.row,
      primaryAction: props.primaryAction,
      actions: props.actions
    })}

    {props.onSelect &&
      <input
        type="checkbox"
        className="data-grid-item-select"
        checked={props.selected}
        onChange={props.onSelect}
      />
    }
  </li>

DataGridItem.propTypes = {
  size: T.string.isRequired,
  orientation: T.string.isRequired,
  row: T.object.isRequired,

  primaryAction: T.shape(
    ActionTypes.propTypes
  ),

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

  card: T.func.isRequired, // It must be a react component.
  selected: T.bool,
  onSelect: T.func
}

DataGridItem.defaultProps = {
  selected: false
}

const DataGridSort = props =>
  <div className="data-grid-sort">
    {trans('list_sort_by')}

    <DropdownButton
      id="data-grid-sort-menu"
      title={props.current.property && getPropDefinition(props.current.property, props.available) ?
        getPropDefinition(props.current.property, props.available).label :
        trans('none')
      }
      bsStyle="link"
      noCaret={true}
      pullRight={true}
    >
      <MenuItem header>{trans('list_columns')}</MenuItem>
      {props.available.map(column =>
        <MenuItem
          key={`sort-by-${column.name}`}
          onClick={() => props.updateSort(column.alias ? column.alias : column.name)}
        >
          {column.label}
        </MenuItem>
      )}
    </DropdownButton>

    <button
      type="button"
      className="btn btn-link"
      disabled={!props.current.property}
      onClick={() => !props.current.property && props.updateSort(props.current.property)}
    >
      <span className={classes('fa fa-fw', {
        'fa-sort'     :  0 === props.current.direction || !props.current.direction,
        'fa-sort-asc' :  1 === props.current.direction,
        'fa-sort-desc': -1 === props.current.direction
      })} />
    </button>
  </div>


DataGridSort.propTypes = {
  current: T.shape({
    property: T.string,
    direction: T.number
  }).isRequired,
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  updateSort: T.func.isRequired
}

const DataGrid = props =>
  <div className={`data-grid data-grid-${props.size} data-grid-${props.orientation}`}>
    {(props.selection || props.sorting) &&
      <div className="data-grid-header">
        {props.selection &&
          <Checkbox
            id="data-grid-select"
            label={trans('list_select_all')}
            labelChecked={trans('list_deselect_all')}
            checked={0 < props.selection.current.length}
            onChange={() => {
              0 === props.selection.current.length ? props.selection.toggleAll(props.data): props.selection.toggleAll([])
            }}
          />
        }

        {1 < props.count && props.sorting &&
          <DataGridSort
            {...props.sorting}
            available={getSortableProps(props.columns)}
          />
        }
      </div>
    }

    {props.selection && 0 < props.selection.current.length &&
      <ListBulkActions
        count={props.selection.current.length}
        actions={getActions(
          props.selection.current.map(id => props.data.find(row => id === row.id) || {id: id}),
          props.actions
        )}
      />
    }

    <ul className="data-grid-content">
      {props.data.map((row) =>
        <DataGridItem
          key={`data-item-${row.id}`}
          size={props.size}
          orientation={props.orientation}
          row={row}
          card={props.card}
          primaryAction={getPrimaryAction(row, props.primaryAction)}
          actions={getActions([row], props.actions)}
          selected={isRowSelected(row, props.selection ? props.selection.current : [])}
          onSelect={
            props.selection ? () => {
              props.selection.toggle(row, !isRowSelected(row, props.selection ? props.selection.current : []))
            } : null
          }
        />
      )}
    </ul>
  </div>

implementPropTypes(DataGrid, DataListView, {
  size: T.oneOf(['sm', 'lg']).isRequired,
  orientation: T.oneOf(['col', 'row']).isRequired,
  card: T.func.isRequired // It must be a react component.
})

export {
  DataGrid
}
