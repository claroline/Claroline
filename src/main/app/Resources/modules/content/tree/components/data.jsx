import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {
  createListDefinition,
  getPrimaryAction,
  getActions,
  getFilterableProps,
  isRowSelected
} from '#/main/app/content/list/utils'
import {
  DataListProperty as DataListPropertyTypes,
  DataListSelection as DataListSelectionTypes,
  DataListSearch as DataListSearchTypes
} from '#/main/app/content/list/prop-types'
import {ListBulkActions} from '#/main/app/content/list/components/actions'
import {ListEmpty} from '#/main/app/content/list/components/empty'
import {ListHeader} from '#/main/app/content/list/components/header'

import {flattenTree} from '#/main/app/content/tree/utils'
import {ContentLoader} from '#/main/app/content/components/loader'

// todo there are some big c/c from data-list
// todo maybe make it a list view
// todo support dynamic actions

const TreeDataItemContent = props =>
  <div className={classes('data-tree-item', {
    'expanded': props.expanded,
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

    {React.createElement(props.card, {
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
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )).isRequired,
  primaryAction: T.shape(
    ActionTypes.propTypes
  ),
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

class TreeDataItem extends Component {
  constructor(props) {
    super(props)

    this.state = {
      expanded: false
    }
  }

  toggle() {
    this.setState({
      expanded: !this.state.expanded
    })
  }

  render() {
    return (
      <li className="data-tree-item-container">
        <TreeDataItemContent
          selected={isRowSelected(this.props.data, this.props.selected)}
          expanded={this.state.expanded}
          hasChildren={this.props.data.children && 0 < this.props.data.children.length}
          data={this.props.data}
          card={this.props.card}
          actions={getActions([this.props.data], this.props.actions)}
          primaryAction={getPrimaryAction(this.props.data, this.props.primaryAction)}
          onSelect={this.props.onSelect ? () => this.props.onSelect(this.props.data) : undefined}
          toggle={() => this.toggle()}
        />

        {this.props.data.children && 0 < this.props.data.children.length &&
          <ul
            className="data-tree-children"
            style={{
              display: this.state.expanded ? 'block':'none'
            }}
          >
            {this.props.data.children.map((child) =>
              <TreeDataItem
                key={child.id}
                data={child}
                actions={this.props.actions}
                primaryAction={this.props.primaryAction}
                selected={this.props.selected}
                onSelect={this.props.onSelect}
                card={this.props.card}
              />
            )}
          </ul>
        }
      </li>
    )
  }
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

class TreeData extends Component {
  constructor(props) {
    super(props)

    // adds missing default in the definition
    this.definition = createListDefinition(this.props.definition)

    this.state = {
      expanded: false
    }
  }

  toggleAll() {

  }

  render() {
    let filtersTool
    if (this.props.filters) {
      filtersTool = Object.assign({}, this.props.filters, {
        available: getFilterableProps(this.definition)
      })
    }

    const flatTree = flattenTree(this.props.data)

    return (
      <div className="data-list">
        <ListHeader
          id={this.props.id}
          disabled={0 === this.props.totalResults}
          filters={filtersTool}
        />

        {this.props.loading &&
          <ContentLoader />
        }

        {(!this.props.loading && 0 === this.props.totalResults) &&
          <ListEmpty hasFilters={this.props.filters && 0 < this.props.filters.current.length} />
        }

        {(!this.props.loading && 0 < this.props.totalResults) &&
          <div className="data-tree">
            <div className="data-tree-header">
              <Button
                type={CALLBACK_BUTTON}
                className="btn-tree-toggle"
                icon={classes('fa fa-fw', {
                  'fa-plus': !this.state.expanded,
                  'fa-minus': this.state.expanded
                })}
                label={trans(this.state.expanded ? 'collapse':'expand', {}, 'actions')}
                tooltip="right"
                callback={() => true}
              />

              {this.props.selection &&
                <Checkbox
                  id="data-tree-select"
                  label={trans('list_select_all')}
                  labelChecked={trans('list_deselect_all')}
                  checked={0 < this.props.selection.current.length}
                  onChange={() => {
                    if (0 === this.props.selection.current.length) {
                      this.props.selection.toggleAll(flatTree)
                    } else {
                      this.props.selection.toggleAll([])
                    }
                  }}
                />
              }
            </div>

            {this.props.selection && 0 < this.props.selection.current.length &&
              <ListBulkActions
                count={this.props.selection.current.length}
                actions={getActions(
                  this.props.selection.current.map(id => flatTree.find(row => id === row.id) || {id: id}),
                  this.props.actions
                )}
              />
            }

            <ul className="data-tree-content">
              {this.props.data.map((row) =>
                <TreeDataItem
                  key={`tree-item-${row.id}`}
                  data={row}
                  actions={this.props.actions}
                  primaryAction={this.props.primaryAction}
                  selected={this.props.selection ? this.props.selection.current : []}
                  onSelect={
                    this.props.selection ? () => {
                      this.props.selection.toggle(row, !isRowSelected(row, this.props.selection ? this.props.selection.current : []))
                    } : undefined
                  }
                  card={this.props.card}
                />
              )}
            </ul>
          </div>
        }
      </div>
    )
  }
}

TreeData.propTypes = {
  id: T.string.isRequired,
  loading: T.bool,

  /**
   * The data tree to display.
   */
  data: T.arrayOf(T.shape({
    // because some features (like selection) requires to retrieves some data rows
    id: T.oneOfType([T.string, T.number]).isRequired,
    // data must be a tree representation
    children: T.array
  })).isRequired,

  /**
   * Total results available in the list (without pagination if any).
   */
  totalResults: T.number.isRequired,

  /**
   * Definition of the data properties.
   */
  definition: T.arrayOf(
    T.shape(DataListPropertyTypes.propTypes)
  ).isRequired,

  /**
   * Actions available for each data row and selected rows (if selection is enabled).
   */
  actions: T.func,

  /**
   * Data primary action (aka open/edit action for rows in most cases).
   * Providing this object will automatically display the primary action (depending on the current view mode).
   */
  primaryAction: T.func,

  /**
   * Search filters configuration.
   * Providing this object automatically display the search box component.
   */
  filters: T.shape(
    DataListSearchTypes.propTypes
  ),

  /**
   * Selection configuration.
   * Providing this object automatically display select checkboxes for each data results.
   */
  selection: T.shape(
    DataListSelectionTypes.propTypes
  ),

  /**
   * A function to normalize data for card display.
   * - the data row is passed as argument
   * - the func MUST return an object respecting `DataCard.propTypes`.
   *
   * It's required to enable cards based display modes.
   */
  card: T.func.isRequired
}

export {
  TreeData
}
