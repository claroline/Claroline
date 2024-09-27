import React, {forwardRef, useMemo} from 'react'
import classes from 'classnames'
import identity from 'lodash/identity'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'

import {MenuOverlay, MenuToggle, Menu, MenuHeader, MenuDivider} from '#/main/app/overlays/menu'
import {MenuAction}  from '#/main/app/buttons/menu/components/menu-action'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const StandardMenu = forwardRef((props, ref) => {
  const isStandard = typeof props.menu === 'object' && props.menu.items

  if (!isStandard) {
    return props.menu
  }

  const actions = useMemo(() => {
    const displayedActions = props.menu.items.filter(
      action => undefined === action.displayed || action.displayed
    )

    // filters and groups actions
    const primaryActions      = displayedActions.filter(action => action.primary && !action.dangerous)
    const unclassifiedActions = displayedActions.filter(action => !action.primary && !action.dangerous && !action.group)
    const dangerousActions    = displayedActions.filter(action => action.dangerous)

    // generate actions groups
    const groupActions = {}
    for (let i=0; i < displayedActions.length; i++) {
      const action = displayedActions[i]
      if (!action.primary && !action.dangerous && !!action.group) {
        if (!groupActions[action.group]) {
          groupActions[action.group] = []
        }

        groupActions[action.group].push(action)
      }
    }

    return {
      primary: primaryActions,
      unclassified: unclassifiedActions,
      groups: groupActions,
      dangerous: dangerousActions
    }
  }, props.menu.items.map(i => i.name))

  return (
    <Menu
      {...omit(props, 'id', 'menu')}
      ref={ref}
      align={'right' === props.menu.align ? 'end' : undefined}
      className={props.className}
    >
      {(props.menu.label && 0 !== actions.unclassified.length) &&
        <MenuHeader>{props.menu.label}</MenuHeader>
      }

      {actions.primary.map((action) =>
        <MenuAction
          {...action}
          key={action.id || action.name || toKey(action.label)}
          id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
        />
      )}

      {(0 !== actions.primary.length && 0 !== actions.unclassified.length) &&
        <MenuDivider />
      }

      {actions.unclassified.map((action) =>
        <MenuAction
          {...action}
          key={action.id || action.name || toKey(action.label)}
          id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
        />
      )}

      {Object.keys(actions.groups).map((group) => [
        <MenuHeader key={toKey(group)}>{group}</MenuHeader>,
        ...actions.groups[group].map((action) =>
          <MenuAction
            {...action}
            key={action.id || action.name || toKey(action.label)}
            id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
          />
        )
      ])}

      {((0 !== actions.primary.length || 0 !== actions.primary.length || 0 !== Object.keys(actions.groups).length) && 0 !== actions.dangerous.length) &&
        <MenuDivider />
      }

      {actions.dangerous.map((action) =>
        <MenuAction
          {...action}
          key={action.id || action.name || toKey(action.label)}
          id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
        />
      )}
    </Menu>
  )
})

StandardMenu.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  name: T.string,
  menu: T.oneOfType([
    // a custom menu component
    T.element,
    // an action menu
    T.shape({
      label: T.string,
      position: T.oneOf(['top', 'bottom']),
      align: T.oneOf(['left', 'right']),
      items: T.arrayOf(T.shape(
        ActionTypes.propTypes
      )).isRequired
    })
  ]).isRequired,

  // appended from react-bootstrap dropdown
  open: T.bool,
  onClose: T.func,
  onSelect: T.func,
  rootCloseEvent: T.oneOf(['click', 'mousedown'])
}

/**
 * Menu button.
 * Renders a component that will open a menu with additional actions.
 */
const MenuButton = forwardRef((props, ref) => {
  const isStandard = typeof props.menu === 'object' && props.menu.items
  let hasActions = false
  if (isStandard) {
    // check there is actions in the menu
    hasActions = !!props.menu.items.find(
      action => undefined === action.displayed || action.displayed
    )
  }

  return (
    <MenuOverlay
      id={props.id}
      show={props.opened}
      position={props.menu.position}
      className={classes(props.containerClassName, 'btn-group')}
      disabled={(isStandard && !hasActions) || props.disabled}
      onToggle={props.onToggle}
      ref={ref}
    >
      <MenuToggle as={CallbackButton} {...omit(props, 'id', 'menu', 'containerClassName', 'onToggle', 'opened', 'onClick')} callback={props.onClick ? props.onClick : identity}>
        {props.children}
      </MenuToggle>
      <StandardMenu
        id={props.id}
        menu={props.menu}
      />
    </MenuOverlay>
  )
})

implementPropTypes(MenuButton, ButtonTypes, {
  id: T.string.isRequired,
  name: T.string,
  opened: T.bool,
  onToggle: T.func,
  containerClassName: T.string, // permits to add a custom class to the wrapping .dropdown element
  menu: T.oneOfType([
    // a custom menu component
    T.element,
    // an action menu
    T.shape({
      label: T.string,
      align: T.oneOf(['left', 'right']),
      position: T.oneOf(['top', 'bottom']),
      items: T.arrayOf(T.shape(
        ActionTypes.propTypes
      )).isRequired
    })
  ]).isRequired
})

export {
  MenuButton
}
