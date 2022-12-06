import React, {createElement, useState, useEffect} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {Checkbox} from '#/main/app/input/components/checkbox'

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
import {flattenTree} from '#/main/app/content/tree/utils'
import merge from 'lodash/merge'

const TreeDataItemContent = props =>
  <div className={classes('data-tree-item', {
    'expanded': props.expanded && props.hasChildren,
    'data-tree-leaf': !props.hasChildren
  })}>
    {props.hasChildren &&
      <Button
        type={CALLBACK_BUTTON}
        className="btn-tree-toggle"
        icon={classes('fa fa-fw', {
          'fa-plus': !props.expanded,
          'fa-minus': props.expanded
        })}
        label={trans(props.expanded ? 'collapse':'expand', {}, 'actions')}
        tooltip="right"
        callback={props.toggle}
      />
    }

    {createElement(props.card, {
      className: classes({selected: props.selected}),
      size: 'sm',
      orientation: 'row',
      data: props.data,
      primaryAction: props.primaryAction,
      actions: props.actions
    })}

    {props.onSelect &&
      <input
        type="checkbox"
        className="data-tree-item-select"
        checked={props.selected}
        onChange={props.onSelect}
      />
    }
  </div>

TreeDataItemContent.propTypes = {
  selected: T.bool.isRequired,
  expanded: T.bool.isRequired,
  hasChildren: T.bool.isRequired,
  primaryAction:  T.oneOfType([
    // a regular action
    T.shape(merge({}, ActionTypes.propTypes, {
      label: T.node // make label optional
    })),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),

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
  data: T.shape({
    id: T.string
  }).isRequired,
  card: T.func.isRequired,
  toggle: T.func,
  onSelect: T.func
}

TreeDataItemContent.defaultProps = {
  selected: false
}

const TreeDataItem = (props) => {
  const [isExpanded, setExpanded] = useState(false)

  useEffect(() => {
    setExpanded(props.expanded)
  }, [props.expanded])

  return (
    <li className="data-tree-item-container">
      <TreeDataItemContent
        selected={isRowSelected(props.data, props.selected)}
        expanded={isExpanded}
        hasChildren={props.data.children && 0 < props.data.children.length}
        data={props.data}
        card={props.card}
        actions={getActions([props.data], props.actions)}
        primaryAction={getPrimaryAction(props.data, props.primaryAction)}
        onSelect={props.onSelect ? () => props.onSelect(props.data) : undefined}
        toggle={() => setExpanded(!isExpanded)}
      />

      {props.data.children && 0 < props.data.children.length &&
        <ul
          className="data-tree-children"
          style={{display: isExpanded ? 'block':'none'}}
        >
          {props.data.children.map((child) =>
            <TreeDataItem
              key={child.id}
              data={child}
              actions={props.actions}
              primaryAction={props.primaryAction}
              selected={props.selected}
              onSelect={props.onSelect}
              card={props.card}
              expanded={props.expanded}
            />
          )}
        </ul>
      }
    </li>
  )
}

TreeDataItem.propTypes = {
  expanded: T.bool,
  selected: T.array,
  data: T.shape({
    id: T.oneOfType([T.string, T.number]).isRequired,
    children: T.array
  }).isRequired,
  primaryAction: T.func,
  actions: T.func,
  onSelect: T.func,
  card: T.func.isRequired
}

TreeDataItem.defaultProps = {
  expanded: false,
  selected: []
}

const DataGridSort = props =>
  <div className="data-tree-sort">
    <span className="hidden-xs">{trans('list_sort_by')}</span>

    <Button
      id="data-tree-sort-menu"
      className="btn-link"
      type={MENU_BUTTON}
      label={props.current.property && getPropDefinition(props.current.property, props.available) ?
        getPropDefinition(props.current.property, props.available).label :
        trans('none')
      }
      primary={true}
      menu={{
        label: trans('list_columns'),
        align: 'right',
        items: props.available.map(column => ({
          type: CALLBACK_BUTTON,
          label: column.label,
          active: props.current.property && (props.current.property === column.alias || props.current.property === column.name),
          callback: () => props.updateSort(column.alias ? column.alias : column.name, props.current.direction || 1)
        }))
      }}
    />

    <Button
      className="btn-link"
      type={CALLBACK_BUTTON}
      icon={classes('fa fa-fw', {
        'fa-sort'     :  0 === props.current.direction || !props.current.direction,
        'fa-sort-asc' :  1 === props.current.direction,
        'fa-sort-desc': -1 === props.current.direction
      })}
      label={trans('sort', {}, 'actions')}
      disabled={!props.current.property}
      callback={() => {
        let direction = 1
        if (1 === props.current.direction) {
          direction = -1
        } else if (-1 === props.current.direction) {
          direction = 0
        }

        props.updateSort(props.current.property, direction)
      }}
      tooltip="left"
      primary={true}
    />
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

const DataTree = props => {
  const [isExpanded, setExpanded] = useState(false)
  const flatTree = flattenTree(props.data)

  return (
    <div className="data-tree">
      {(props.selection || props.sorting) &&
        <div className="data-tree-header">
          <Button
            type={CALLBACK_BUTTON}
            className="btn-tree-toggle"
            icon={classes('fa fa-fw', {
              'fa-plus': !isExpanded,
              'fa-minus': isExpanded
            })}
            label={trans(isExpanded ? 'collapse':'expand', {}, 'actions')}
            tooltip="right"
            callback={() => setExpanded(!isExpanded)}
          />

          {props.selection &&
            <Checkbox
              id="data-tree-select"
              label={<span className="hidden-xs">{trans('list_select_all')}</span>}
              labelChecked={<span className="hidden-xs">{trans('list_deselect_all')}</span>}
              checked={0 < props.selection.current.length}
              onChange={() => {
                if (0 === props.selection.current.length) {
                  props.selection.toggleAll(flatTree)
                } else {
                  props.selection.toggleAll([])
                }
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
            props.selection.current.map(id => flatTree.find(row => id === row.id) || {id: id}),
            props.actions
          )}
        />
      }

      <ul className="data-tree-content">
        {props.data.map((row) =>
          <TreeDataItem
            key={`tree-item-${row.id}`}
            data={row}
            actions={props.actions}
            primaryAction={props.primaryAction}
            selected={props.selection ? props.selection.current : []}
            expanded={isExpanded}
            onSelect={
              props.selection ? () => {
                props.selection.toggle(row, !isRowSelected(row, props.selection ? props.selection.current : []))
              } : undefined
            }
            card={props.card}
          />
        )}
      </ul>
    </div>
  )
}

implementPropTypes(DataTree, DataListView, {
  size: T.oneOf(['sm', 'lg']).isRequired,
  orientation: T.oneOf(['col', 'row']).isRequired,
  card: T.func.isRequired // It must be a react component.
})

export {
  DataTree
}
