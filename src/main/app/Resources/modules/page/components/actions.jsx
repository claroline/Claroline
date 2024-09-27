import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {Button} from '#/main/app/action'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {PageTitle} from '#/main/app/page/components/title'
import {PageMenu} from '#/main/app/page/components/menu'
import {Await} from '#/main/app/components/await'

const PageActions = (props) => {
  if (isEmpty(props.actions)) {
    return null
  }

  let actions = [].concat(props.actions)

  let primaryAction
  if (props.primaryAction) {
    const primaryPos = actions.findIndex(action => action.name === props.primaryAction)
    if (-1 !== primaryPos) {
      primaryAction = actions[primaryPos]
      actions.splice(primaryPos, 1)
    }
  }

  let secondaryAction
  if (props.secondaryAction) {
    const secondaryPos = actions.findIndex(action => action.name === props.secondaryAction)
    if (-1 !== secondaryPos) {
      secondaryAction = actions[secondaryPos]
      actions.splice(secondaryPos, 1)
    }
  }

  if (!secondaryAction && 1 === actions.length) {
    secondaryAction = actions[0]
    actions.splice(0, 1)
  }

  return (
    <div className="page-actions gap-2 ms-auto d-flex flex-nowrap" role="presentation">
      {primaryAction && (undefined === primaryAction.displayed || primaryAction.displayed) &&
        <Button
          {...primaryAction}
          className="btn btn-primary page-action-btn"
          icon={undefined}
          tooltip={undefined}
          disabled={props.disabled}
        />
      }

      {secondaryAction && (undefined === secondaryAction.displayed || secondaryAction.displayed) &&
        <Button
          {...secondaryAction}
          className="btn btn-body page-actions-btn"
          icon={undefined}
          tooltip={undefined}
          disabled={props.disabled}
        />
      }

      {!isEmpty(actions) &&
        <Toolbar
          id="page-actions-toolbar"
          className="btn-toolbar gap-1 flex-nowrap"
          buttonName="btn btn-body page-actions-btn"
          tooltip="bottom"
          toolbar={props.toolbar}
          actions={actions}
          disabled={props.disabled}
          scope="object"
        />
      }
    </div>
  )
}

PageActions.propTypes = {
  disabled: T.bool,

  /**
   * The name of an optional primary action of the page.
   * NB. The action MUST be defined in the `actions` list.
   */
  primaryAction: T.string,

  /**
   * The name of an optional secondary action of the page.
   * NB. The action MUST be defined in the `actions` list.
   */
  secondaryAction: T.string,

  toolbar: T.string,

  /**
   * The list of actions available for the current page.
   * NB. This list MUST contain the actions for `primaryAction` and `secondaryAction` if defined.
   *
   * @type {Array}
   */
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ])
}

export {
  PageActions
}
