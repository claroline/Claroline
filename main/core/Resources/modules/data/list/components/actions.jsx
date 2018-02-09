import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {DropdownButton, MenuItem} from 'react-bootstrap'

import {t, transChoice} from '#/main/core/translation'
import {MenuItemAction} from '#/main/core/layout/components/dropdown.jsx'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'

import {DataListAction as DataListActionTypes} from '#/main/core/data/list/prop-types'

// todo force `action` to be a function that generate the final action so we can get urls

const ListPrimaryAction = props => {
  let disabled = true
  let action = null

  if (props.action) {
    disabled = props.action.disabled ? props.action.disabled(props.item) : false
    action = props.action.action(props.item)
  }

  if (disabled) {
    return React.createElement(props.disabledWrapper, {className: props.className}, props.children)
  } else {
    if (typeof action === 'string') {
      return (
        <a role="link" href={action} className={props.className}>
          {props.children}
        </a>
      )
    } else {
      return (
        <a role="button" onClick={action} className={props.className}>
          {props.children}
        </a>
      )
    }
  }
}

ListPrimaryAction.propTypes = {
  className: T.string,
  item: T.object.isRequired,
  action: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),
  disabledWrapper: T.string,
  children: T.any.isRequired
}

ListPrimaryAction.defaultProps = {
  disabled: false,
  disabledWrapper: 'span'
}

/**
 * Actions available for a single data item.
 *
 * @param props
 * @constructor
 */
const ListActions = props => {
  const displayedActions = props.actions.filter(
    action => !action.displayed || action.displayed([props.item])
  )

  const actions          = displayedActions.filter(action => !action.dangerous)
  const dangerousActions = displayedActions.filter(action =>  action.dangerous)

  // only display button if there are actions
  return (0 !== displayedActions.length) && (
    <TooltipElement
      id={`${props.id}-tip`}
      tip={t('actions')}
      position="left"
    >
      <DropdownButton
        id={`${props.id}-btn`}
        title={<span className="fa fa-fw fa-ellipsis-v" />}
        className="data-actions-btn btn-link-default"
        bsStyle="link"
        noCaret={true}
        pullRight={true}
      >
        <MenuItem header>{t('actions')}</MenuItem>

        {actions.map((action, actionIndex) =>
          <MenuItemAction
            key={`${props.id}-action-${actionIndex}`}
            icon={action.icon}
            label={action.label}
            disabled={action.disabled ? action.disabled([props.item]) : false}
            action={typeof action.action === 'function' ? () => action.action([props.item]) : action.action}
          />
        )}

        {(0 !== actions.length && 0 !== dangerousActions.length) &&
          <MenuItem divider />
        }

        {dangerousActions.map((action, actionIndex) =>
          <MenuItemAction
            key={`${props.id}-action-dangerous-${actionIndex}`}
            icon={action.icon}
            label={action.label}
            disabled={action.disabled ? action.disabled([props.item]) : false}
            action={typeof action.action === 'function' ? () => action.action([props.item]) : action.action}
            dangerous={true}
          />
        )}
      </DropdownButton>
    </TooltipElement>
  )
}

ListActions.propTypes = {
  id: T.string.isRequired,
  item: T.object.isRequired,
  actions: T.arrayOf(
    T.shape(DataListActionTypes.propTypes)
  ).isRequired
}

/**
 * Bulk actions available for selected data items.
 *
 * @param props
 * @constructor
 */
const ListBulkActions = props =>
  <div className="data-bulk-actions list-selected">
    <div className="list-selected-label">
      <span className="fa fa-level-up fa-rotate-90" />
      {transChoice('list_selected_count', props.count, {count: props.count}, 'platform')}
    </div>

    <div className="list-selected-actions">
      {props.actions
        .filter(action => action.displayed ? action.displayed(props.selectedItems) : true)
        .map((action, actionIndex) =>
          <TooltipAction
            id={`list-bulk-action-${actionIndex}`}
            key={`list-bulk-action-${actionIndex}`}
            className={classes({
              'btn-link-default': !action.dangerous,
              'btn-link-danger' :  action.dangerous
            })}
            icon={action.icon}
            label={action.label}
            disabled={action.disabled ? action.disabled(props.selectedItems) : false}
            action={typeof action.action === 'function' ? () => action.action(props.selectedItems) : action.action}
          />
        )
      }
    </div>
  </div>

ListBulkActions.propTypes = {
  count: T.number.isRequired,
  selectedItems: T.arrayOf(T.object).isRequired,
  actions: T.arrayOf(
    T.shape(DataListActionTypes.propTypes)
  ).isRequired
}

export {
  ListActions,
  ListBulkActions,
  ListPrimaryAction
}
