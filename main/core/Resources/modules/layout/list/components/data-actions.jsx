import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {DropdownButton, MenuItem} from 'react-bootstrap'

import {t, transChoice} from '#/main/core/translation'
import {MenuItemAction} from '#/main/core/layout/components/dropdown.jsx'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TooltipLink} from '#/main/core/layout/button/components/tooltip-link.jsx'

import {DataAction as DataActionTypes} from '#/main/core/layout/list/prop-types'

/**
 * Actions available for a single data item.
 *
 * @param props
 * @constructor
 */
const DataActions = props =>
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

      {props.actions.filter(
        action => !action.dangerous && (!action.displayed || action.displayed([props.item]))
      ).map((action, actionIndex) =>
        <MenuItemAction
          key={`${props.id}-action-${actionIndex}`}
          icon={action.icon}
          label={action.label}
          disabled={action.disabled ? action.disabled([props.item]) : false}
          action={typeof action.action === 'function' ? () => action.action([props.item]) : action.action}
        />
      )}

      {0 !== props.actions.filter(action => action.dangerous && (!action.displayed || action.displayed([props.item]))).length &&
        <MenuItem divider />
      }

      {props.actions.filter(
        action => action.dangerous && (!action.displayed || action.displayed([props.item]))
      ).map((action, actionIndex) =>
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

DataActions.propTypes = {
  id: T.string.isRequired,
  item: T.object.isRequired,
  actions: T.arrayOf(
    T.shape(DataActionTypes.propTypes)
  ).isRequired
}

/**
 * Bulk actions available for selected data items.
 *
 * @param props
 * @constructor
 */
const DataBulkActions = props =>
  <div className="data-bulk-actions list-selected">
    <div className="list-selected-label">
      <span className="fa fa-level-up fa-rotate-90" />
      {transChoice('list_selected_count', props.count, {count: props.count}, 'platform')}
    </div>

    <div className="list-selected-actions">
      {props.actions.map((action, actionIndex) => typeof action.action === 'function' ?
        <TooltipButton
          id={`list-bulk-action-${actionIndex}`}
          key={`list-bulk-action-${actionIndex}`}
          className={classes({
            'btn-link-default': !action.dangerous,
            'btn-link-danger' :  action.dangerous
          })}
          title={action.label}
          disabled={action.disabled ? action.disabled(props.selectedItems) : false}
          onClick={() => action.action(props.selectedItems)}
        >
          <span className={action.icon} />
          <span className="sr-only">{action.label}</span>
        </TooltipButton>
        :
        <TooltipLink
          id={`list-bulk-action-${actionIndex}`}
          key={`list-bulk-action-${actionIndex}`}
          className={classes({
            'btn-link-default': !action.dangerous,
            'btn-link-danger' :  action.dangerous
          })}
          title={action.label}
          disabled={action.disabled ? action.disabled(props.selectedItems) : false}
          target={action.action}
        >
          <span className={action.icon} />
          <span className="sr-only">{action.label}</span>
        </TooltipLink>
      )}
    </div>
  </div>

DataBulkActions.propTypes = {
  count: T.number.isRequired,
  selectedItems: T.arrayOf(T.object).isRequired,
  actions: T.arrayOf(
    T.shape(DataActionTypes.propTypes)
  ).isRequired
}

export {
  DataActions,
  DataBulkActions
}
