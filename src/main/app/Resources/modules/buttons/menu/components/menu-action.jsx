import React, {createElement, forwardRef} from 'react'
import classes from 'classnames'
import invariant from 'invariant'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {MenuItem} from '#/main/app/overlays/menu'
import {registry as buttonRegistry} from '#/main/app/buttons/registry'

const MenuButton = forwardRef((props, ref) => {
  const button = buttonRegistry.get(props.type)

  invariant(undefined !== button, `You have requested a non existent button "${props.type}".`)

  let subscript
  if (props.subscript) {
    if ('text' === props.subscript.type) {
      subscript = <span key="button-subscript" className={classes('ms-auto action-subscript', `text-${props.subscript.status || 'primary'}`)}>{props.subscript.value}</span>
    } else {
      subscript = <span key="button-subscript" className={classes('ms-auto action-subscript badge', `text-bg-${props.subscript.status || 'primary'}`)}>{props.subscript.value}</span>
    }
  }

  return createElement(button, merge(omit(props, 'type', 'icon', 'label', 'subscript', 'managerOnly', 'description'), {ref: ref}), (
    <>
      {(props.icon && typeof props.icon === 'string') ?
        <span className={classes('action-icon', props.icon)} aria-hidden={true} /> :
        props.icon
      }

      {props.label}
      {props.children}
      {subscript}
    </>
  ))
})

implementPropTypes(MenuButton, ActionTypes)

const MenuAction = (props) => {
  return (
    <MenuItem
      as={MenuButton}
      {...props}
      className={classes(props.className, 'd-flex align-items-center gap-3 focus-ring')}
    />
  )
}

implementPropTypes(MenuAction, ActionTypes, {
  // from standard dropdown MenuItem
  eventKey: T.string,
  onSelect: T.func
})

export {
  MenuAction
}
