import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {DropdownButton, MenuItem} from 'react-bootstrap'

import {t} from '#/main/core/translation'
import {getPlainText} from '#/main/core/layout/data/types/html/utils'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox.jsx'
import {DataAction, DataCard, DataProperty, DataListView} from '#/main/core/layout/list/prop-types'
import {getBulkActions, getRowActions, getPropDefinition, getSortableProps, isRowSelected} from '#/main/core/layout/list/utils'
import {DataActions, DataBulkActions} from '#/main/core/layout/list/components/data-actions.jsx'

const DataGridItem = props =>
  <div className={classes('data-grid-item', props.data.className, {selected: props.selected})}>
    {props.onSelect &&
      <input
        type="checkbox"
        className="item-select"
        checked={props.selected}
        onChange={props.onSelect}
      />
    }

    <div className="item-header">
      <span className="item-icon-container">
        {typeof props.data.icon === 'string' ?
          <span className={props.data.icon} />
         :
          props.data.icon
        }
      </span>

      {props.data.flags &&
        <div className="item-flags">
          {props.data.flags.map((flag, flagIndex) => flag &&
            <TooltipElement
              key={flagIndex}
              id={`item-${props.index}-flag-${flagIndex}`}
              tip={flag[1]}
            >
              <span className={classes('item-flag', flag[0])} />
            </TooltipElement>
          )}
        </div>
      }
    </div>

    {React.createElement(
      props.data.onClick ? 'a' : 'div', {
        className: 'item-content',
        [typeof props.data.onClick === 'function' ? 'onClick':'href']: props.data.onClick
      }, [
        // card title
        <h2 key="item-title" className="item-title">
          {props.data.title}
          {props.data.subtitle &&
            <small>{props.data.subtitle}</small>
          }
        </h2>,

        // card detail text
        'sm' !== props.size && props.data.contentText &&
        <div key="item-description" className="item-description">
          {getPlainText(props.data.contentText)}
        </div>,

        // card footer
        props.data.footer &&
        <div key="item-footer" className="item-footer">
          {'sm' !== props.size && props.data.footerLong ?
            props.data.footerLong : props.data.footer
          }
        </div>
      ]
    )}

    {props.actions &&
      <DataActions
        id={`data-grid-item-${props.index}-actions`}
        item={props.row}
        actions={props.actions}
      />
    }
  </div>

DataGridItem.propTypes = {
  index: T.number.isRequired,
  size: T.string.isRequired,
  row: T.object.isRequired,

  /**
   * Computed card data from row.
   */
  data: T.shape(
    DataCard.propTypes
  ).isRequired,

  actions: T.arrayOf(
    T.shape(DataAction.propTypes)
  ),
  selected: T.bool,
  onSelect: T.func
}

DataGridItem.defaultProps = {
  selected: false
}

const DataGridSort = props =>
  <div className="data-grid-sort">
    {t('list_sort_by')}
    <DropdownButton
      id="data-grid-sort-menu"
      title={getPropDefinition(props.current.property, props.available).label}
      bsStyle="link"
      noCaret={true}
      pullRight={true}
    >
      <MenuItem header>{t('list_columns')}</MenuItem>
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
      onClick={() => props.updateSort(props.current.property)}
    >
      <span className={classes('fa fa-fw', {
        'fa-sort'     :  0 === props.current.direction,
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
    T.shape(DataProperty.propTypes)
  ).isRequired,
  updateSort: T.func.isRequired
}

const DataGrid = props =>
  <div className={`data-grid data-grid-${props.size}`}>
    <div className="data-grid-header">
      {1 < props.count && props.selection &&
        <Checkbox
          id="data-grid-select"
          label={t('list_select_all')}
          labelChecked={t('list_deselect_all')}
          checked={0 < props.selection.current.length}
          onChange={() => props.selection.toggleAll(props.data)}
        />
      }

      {1 < props.count && props.sorting &&
        <DataGridSort
          {...props.sorting}
          available={getSortableProps(props.columns)}
        />
      }
    </div>

    {props.selection && 0 < props.selection.current.length &&
      <DataBulkActions
        count={props.selection.current.length}
        selectedItems={props.selection.current.map(id => props.data.find(row => id === row.id))}
        actions={getBulkActions(props.actions)}
      />
    }

    <ul className="data-grid-content">
      {props.data.map((row, rowIndex) =>
        <li className="data-grid-item-container" key={`data-item-${rowIndex}`}>
          <DataGridItem
            index={rowIndex}
            size={props.size}
            row={row}
            data={props.card(row)}
            actions={getRowActions(props.actions)}
            selected={isRowSelected(row, props.selection ? props.selection.current : [])}
            onSelect={1 < props.count && props.selection ? () => props.selection.toggle(row) : null}
          />
        </li>
      )}
    </ul>
  </div>

DataGrid.propTypes    = DataListView.propTypes
DataGrid.defaultProps = DataListView.defaultProps

export {
  DataGrid
}
