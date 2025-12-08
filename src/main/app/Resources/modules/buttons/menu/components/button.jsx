import React, {forwardRef, useMemo} from 'react'
import classes from 'classnames'
import identity from 'lodash/identity'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/app/utils/text'

import {MenuOverlay, MenuToggle, Menu, MenuHeader, MenuDivider} from '#/main/app/overlays/menu'
import {MenuAction}  from '#/main/app/buttons/menu/components/menu-action'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const StandardMenu = (props) => {
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
  }, [props.menu.items.map(item => item.name).join('-')])

  return (
    <Menu
      {...omit(props, 'menu')}
      className={props.menu.className}
      style={props.menu.style}
    >
      {(props.menu.label && 0 !== actions.unclassified.length) &&
        <MenuHeader>{props.menu.label}</MenuHeader>
      }

      {actions.primary.map((action) =>
        <MenuAction
          {...action}
          key={action.name || toKey(action.label)}
        />
      )}

      {(0 !== actions.primary.length && 0 !== actions.unclassified.length) &&
        <MenuDivider />
      }

      {actions.unclassified.map((action) =>
        <MenuAction
          {...action}
          key={action.name || toKey(action.label)}
        />
      )}

      {Object.keys(actions.groups).map((group, i) => [
        (0 !== i || 0 !== actions.primary.length || 0 !== actions.unclassified.length) && <MenuDivider key={group} />,
        ...actions.groups[group].map((action) =>
          <MenuAction
            {...action}
            key={action.name || toKey(action.label)}
          />
        )
      ])}

      {((0 !== actions.primary.length || 0 !== actions.primary.length || 0 !== Object.keys(actions.groups).length) && 0 !== actions.dangerous.length) &&
        <MenuDivider />
      }

      {actions.dangerous.map((action) =>
        <MenuAction
          {...action}
          key={action.name || toKey(action.label)}
        />
      )}
    </Menu>
  )
}

StandardMenu.propTypes = {
  menu: T.shape({
    className: T.string,
    label: T.string,
    style: T.object,
    position: T.oneOf(['top', 'bottom']),
    align: T.oneOf(['start', 'end']),
    items: T.arrayOf(T.shape(
      ActionTypes.propTypes
    )).isRequired
  }).isRequired,

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
  let menuComponent
  if (isStandard) {
    // check there is actions in the menu
    hasActions = !!props.menu.items.find(
      action => undefined === action.displayed || action.displayed
    )
  } else {
    if (typeof props.menu === 'object' && props.menu.render) {
      menuComponent = props.menu.render()
    } else {
      menuComponent = props.menu
    }
  }

  return (
    <MenuOverlay
      show={props.opened}
      position={props.menu.position}
      align={props.menu.align}
      drop={props.menu.drop}
      className={classes(props.containerClassName, 'btn-group')}
      style={props.containerStyle}
      disabled={(isStandard && !hasActions) || props.disabled}
      onToggle={props.onToggle}
      ref={ref}
    >
      <MenuToggle
        {...omit(props, 'menu', 'containerClassName', 'containerStyle', 'onToggle', 'opened', 'onClick', 'indicator')}
        as={CallbackButton}
        callback={props.onClick ? props.onClick : identity}
      >
        {props.children}

        {props.indicator &&
          <small className="fa fa-chevron-down opacity-50 ms-2" aria-hidden={true} />
        }
      </MenuToggle>

      {isStandard ?
        <StandardMenu
          menu={props.menu}
        /> :
        menuComponent
      }
    </MenuOverlay>
  )
})

implementPropTypes(MenuButton, ButtonTypes, {
  opened: T.bool,
  onToggle: T.func,
  containerClassName: T.string, // permits to add a custom class to the wrapping .dropdown element,
  containerStyle: T.object, // permits to add a custom styles to the wrapping .dropdown element,
  indicator: T.bool,
  menu: T.oneOfType([
    // a custom menu component
    T.element,
    // an action menu
    T.shape({
      className: T.string,
      label: T.string,
      drop: T.oneOf(['up', 'start', 'end', 'down']),
      align: T.oneOf(['start', 'end']),
      items: T.arrayOf(T.shape(
        ActionTypes.propTypes
      )),
      render: T.func
    })
  ]).isRequired
})

export {
  MenuButton
}
