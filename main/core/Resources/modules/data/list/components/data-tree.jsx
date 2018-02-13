import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {t, trans, transChoice} from '#/main/core/translation'

import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer.jsx'
import {makeDroppable, makeDraggable} from '#/plugin/exo/utils/dragAndDrop'

import {constants as listConst} from '#/main/core/data/list/constants'
import {
  createListDefinition,
  getBulkActions,
  getRowActions,
  getFilterableProps,
  isRowSelected
} from '#/main/core/data/list/utils'
import {
  DataListAction as DataListActionTypes,
  DataCard as DataCardTypes,
  DataListProperty as DataListPropertyTypes,
  DataListSelection as DataListSelectionTypes,
  DataListSearch as DataListSearchTypes
} from '#/main/core/data/list/prop-types'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox.jsx'
import {ListActions, ListPrimaryAction, ListBulkActions} from '#/main/core/data/list/components/actions.jsx'
import {ListEmpty} from '#/main/core/data/list/components/empty.jsx'
import {ListHeader} from '#/main/core/data/list/components/header.jsx'

// todo there are some big c/c from data-list
// todo maybe make it a list view

const DataTreeItemContent = props =>
  <div className={classes('data-tree-item', props.computedData.className, {
    'expanded': props.expanded,
    'selected': props.selected,
    'data-tree-leaf': !props.hasChildren
  })}>
    {props.hasChildren &&
      <button
        type="button"
        className="btn btn-tree-toggle"
        onClick={props.toggle}
      >
        <span className={classes('fa fa-fw', {
          'fa-plus': !props.expanded,
          'fa-minus': props.expanded
        })} />
      </button>
    }

    <div className="data-tree-item-content">
      <div className="data-tree-item-label">
        {props.onSelect &&
          <input
            type="checkbox"
            className="data-tree-item-select"
            checked={props.selected}
            onChange={props.onSelect}
          />
        }

        <ListPrimaryAction
          item={props.data}
          action={props.primaryAction}
          className="item-title"
          disabledWrapper="h2"
        >
          {[
            props.computedData.title,
            props.computedData.subtitle &&
            <small key="item-subtitle">{props.computedData.subtitle}</small>
          ]}
        </ListPrimaryAction>

        {props.computedData.flags &&
          <div className="item-flags">
            {props.computedData.flags.map((flag, flagIndex) => flag &&
              <TooltipElement
                key={flagIndex}
                id={`item-${props.data.id}-flag-${flagIndex}`}
                tip={flag[1]}
              >
                <span className={classes('item-flag', flag[0])} />
              </TooltipElement>
            )}
          </div>
        }
      </div>

      {0 < props.actions.length &&
        <ListActions
          id={`actions-${props.data.id}`}
          item={props.data}
          actions={props.actions}
        />
      }

      {props.connectDragSource && props.connectDragSource(
        <span
          className="btn data-actions-btn btn-drag"
        >
          <span className="fa fa-fw fa-arrows" />
          <span className="sr-only">{t('move')}</span>
        </span>
      )}
    </div>
  </div>

DataTreeItemContent.propTypes = {
  selected: T.bool.isRequired,
  expanded: T.bool.isRequired,
  hasChildren: T.bool.isRequired,
  actions: T.array.isRequired,
  primaryAction: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),
  data: T.shape({
    id: T.string
  }).isRequired,
  /**
   * Computed card data from row.
   */
  computedData: T.shape(
    DataCardTypes.propTypes
  ).isRequired,
  toggle: T.func,
  onSelect: T.func,

  connectDragSource: T.func
}

DataTreeItemContent.defaultProps = {
  selected: false
}

let DataTreeItemContainer = props => props.connectDropTarget(
  <li className="data-tree-item-container" style={{opacity: props.isDragging ? 0.5:1}}>
    {React.Children.map(props.children, (child, index) => child ?
      React.cloneElement(child, {
        key: index,
        connectDragSource: props.connectDragSource
      }) : null
    )}
  </li>
)

DataTreeItemContainer.propTypes = {
  isDragging: T.bool.isRequired,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired,
  children: T.any.isRequired
}

const DataPreview = () =>
  <div className="data-tree-item">
    IM A DRAG PREVIEW !!!! SEE ME
  </div>

DataTreeItemContainer = makeDraggable(
  DataTreeItemContainer,
  'TREE_ITEM',
  DataPreview
)

DataTreeItemContainer = makeDroppable(
  DataTreeItemContainer,
  'TREE_ITEM'
)

class DataTreeItemComponent extends Component {
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
      <DataTreeItemContainer onDrop={this.props.onDrop}>
        <DataTreeItemContent
          selected={isRowSelected(this.props.data, this.props.selected)}
          expanded={this.state.expanded}
          hasChildren={this.props.data.children && 0 < this.props.data.children.length}
          data={this.props.data}
          computedData={this.props.card(this.props.data)}
          actions={this.props.actions}
          primaryAction={this.props.primaryAction}
          onSelect={this.props.onSelect ? () => this.props.onSelect(this.props.data) : undefined}
          toggle={() => this.toggle()}
          connectDragSource={this.props.connectDragSource}
        />

        {this.props.data.children && 0 < this.props.data.children.length &&
          <ul
            className="data-tree-children"
            style={{
              display: this.state.expanded ? 'block':'none'
            }}
          >
            {this.props.data.children.map((child, childIndex) =>
              <DataTreeItem
                key={`tree-child-${childIndex}`}
                data={child}
                actions={this.props.actions}
                primaryAction={this.props.primaryAction}
                selected={this.props.selected}
                onSelect={this.props.onSelect}
                card={this.props.card}
                onDrop={this.props.onDrop}
              />
            )}
          </ul>
        }
      </DataTreeItemContainer>
    )
  }
}

DataTreeItemComponent.propTypes = {
  expanded: T.bool,
  selected: T.array,
  data: T.shape({
    id: T.oneOfType([T.string, T.number]).isRequired,
    children: T.array
  }).isRequired,
  primaryAction: T.object,
  actions: T.arrayOf(
    T.shape(DataListActionTypes.propTypes)
  ).isRequired,

  onSelect: T.func,

  onDrop: T.func,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired,

  card: T.func.isRequired
}

DataTreeItemComponent.defaultProps = {
  expanded: false,
  selected: []
}

let DataTreeItem = makeDraggable(
  DataTreeItemComponent,
  'TREE_ITEM'
)

DataTreeItem = makeDroppable(
  DataTreeItem,
  'TREE_ITEM'
)

class DataTree extends Component {
  constructor(props) {
    super(props)

    // adds missing default in the definition
    this.definition = createListDefinition(this.props.definition)
    // fills missing translations with default ones
    this.translations = merge({}, listConst.DEFAULT_TRANSLATIONS, this.props.translations)

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

    // calculate actions
    let actions = this.props.actions.slice(0)
    if (this.props.deleteAction) {
      actions.push({
        icon: 'fa fa-fw fa-trash-o',
        label: t('delete'),
        dangerous: true,
        displayed: this.props.deleteAction.displayed,
        disabled: this.props.deleteAction.disabled,
        action: typeof this.props.deleteAction.action === 'function' ?
          (rows) => this.props.deleteAction.action(
            rows,
            trans(this.translations.keys.deleteConfirmTitle, {}, this.translations.domain),
            transChoice(this.translations.keys.deleteConfirmQuestion, rows.length, {count: rows.length}, this.translations.domain)
          ) :
          this.props.deleteAction.action
      })
    }

    return (
      <div className="data-list">
        <ListHeader
          disabled={0 === this.props.totalResults}
          filters={filtersTool}
        />

        {0 < this.props.totalResults &&
          <div className="data-tree">
            <div className="data-tree-header">
              <button
                type="button"
                className="btn btn-tree-toggle"
                onClick={() => true}
              >
                <span className={classes('fa fa-fw', {
                  'fa-plus': !this.state.expanded,
                  'fa-minus': this.state.expanded
                })} />
              </button>

              {this.props.selection &&
                <Checkbox
                  id="data-tree-select"
                  label={t('list_select_all')}
                  labelChecked={t('list_deselect_all')}
                  checked={0 < this.props.selection.current.length}
                  onChange={() => this.props.selection.toggleAll(this.props.data)}
                />
              }
            </div>

            {this.props.selection && 0 < this.props.selection.current.length &&
              <ListBulkActions
                count={this.props.selection.current.length}
                selectedItems={this.props.selection.current.map(id => this.props.data.find(row => id === row.id) || {id: id})}
                actions={getBulkActions(actions)}
              />
            }

            <ul className="data-tree-content">
              {this.props.data.map((row, rowIndex) =>
                <DataTreeItem
                  key={`tree-item-${rowIndex}`}
                  data={row}
                  actions={getRowActions(actions)}
                  primaryAction={this.props.primaryAction}
                  selected={this.props.selection ? this.props.selection.current : []}
                  onSelect={this.props.selection ? this.props.selection.toggle : undefined}
                  card={this.props.card}

                  onDrop={() => {

                  }}
                />
              )}
            </ul>
          </div>
        }

        {0 === this.props.totalResults &&
          <ListEmpty hasFilters={this.props.filters && 0 < this.props.filters.current.length} />
        }
        <CustomDragLayer/>
      </div>
    )
  }
}

DataTree.propTypes = {
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
  actions: T.arrayOf(
    T.shape(DataListActionTypes.propTypes)
  ),

  /**
   * Data primary action (aka open/edit action for rows in most cases).
   * Providing this object will automatically display the primary action (depending on the current view mode).
   */
  primaryAction: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),

  /**
   * Data delete action.
   * Providing this object will automatically append the delete action to the actions list of rows and selection.
   */
  deleteAction: T.shape({
    disabled: T.func,
    displayed: T.func,
    // if a function is provided, it receive the `rows`, `confirmTitle`, `confirmQuestion` as param
    action: T.oneOfType([T.string, T.func]).isRequired
  }),

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

  reorder: T.shape({

  }),

  /**
   * A function to normalize data for card display.
   * - the data row is passed as argument
   * - the func MUST return an object respecting `DataCard.propTypes`.
   *
   * It's required to enable cards based display modes.
   */
  card: T.func.isRequired,

  /**
   * Override default list translations.
   */
  translations: T.shape({
    domain: T.string,
    keys: T.shape({
      searchPlaceholder: T.string,
      emptyPlaceholder: T.string,
      countResults: T.string,
      deleteConfirmTitle: T.string,
      deleteConfirmQuestion: T.string
    })
  })
}

DataTree.defaultProps = {
  actions: [],
  translations: listConst.DEFAULT_TRANSLATIONS
}

export {
  DataTree
}
