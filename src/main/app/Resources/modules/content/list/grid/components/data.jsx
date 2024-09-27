import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {DataListView} from '#/main/app/content/list/prop-types'
import {
  getPrimaryAction,
  getActions,
  getSortableProps,
  isRowSelected
} from '#/main/app/content/list/utils'
import {ListBulkActions} from '#/main/app/content/list/components/actions'

import {GridItem} from '#/main/app/content/list/grid/components/item'
import {GridSort} from '#/main/app/content/list/grid/components/sort'

const GridData = props =>
  <div className={`data-grid data-grid-${props.size} data-grid-${props.orientation}`}>
    {(props.selection || props.sorting) &&
      <div className="data-grid-header">
        {props.selection &&
          <Checkbox
            id="data-grid-select"
            className="py-2 m-0"
            label={<span className="d-none d-sm-block">{trans('list_select_all')}</span>}
            labelChecked={<span className="d-none d-sm-block">{trans('list_deselect_all')}</span>}
            checked={0 < props.selection.current.length}
            onChange={() => {
              if (0 === props.selection.current.length) {
                props.selection.toggleAll(props.data)
              } else {
                props.selection.toggleAll([])
              }
            }}
          />
        }

        {1 < props.count && props.sorting &&
          <GridSort
            {...props.sorting}
            available={getSortableProps(props.definition)}
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

    <ul className="data-grid-content list-unstyled mb-auto">
      {props.data.map((row) =>
        <GridItem
          key={row.id}
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

implementPropTypes(GridData, DataListView, {
  size: T.oneOf(['sm', 'lg']).isRequired,
  orientation: T.oneOf(['col', 'row']).isRequired,
  card: T.func.isRequired // It must be a React component.
})

export {
  GridData
}
