import React, {Component} from 'react'
import classes from 'classnames'
import identity from 'lodash/identity'
import omit from 'lodash/omit'

import RootCloseWrapper from 'react-overlays/lib/RootCloseWrapper'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'

import {MenuOverlay, Menu, MenuItem} from '#/main/app/overlays/menu'
import {MenuAction}  from '#/main/app/buttons/menu/components/menu-action'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'


// The class is only here because react-bootstrap dropdown uses ref to work and
// it's not possible on stateless components
class StandardMenu extends Component {
  constructor(props) {
    super(props)

    this.handleRootClose = this.handleRootClose.bind(this)
  }

  // for custom menus
  handleRootClose(event) {
    this.props.onClose(event, { source: 'rootClose' })
  }

  render() {
    const props = this.props

    const isStandard = typeof props.menu === 'object' && props.menu.items

    if (isStandard) {
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

      return (
        <Menu {...omit(props, 'id', 'menu')}>
          {(props.menu.label && 0 !== unclassifiedActions.length) &&
            <MenuItem header={true}>{props.menu.label}</MenuItem>
          }

          {primaryActions.map((action) =>
            <MenuAction
              {...action}
              key={action.id || action.name || toKey(action.label)}
              id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
            />
          )}

          {(0 !== primaryActions.length && 0 !== unclassifiedActions.length) &&
            <MenuItem divider={true} />
          }

          {unclassifiedActions.map((action) =>
            <MenuAction
              {...action}
              key={action.id || action.name || toKey(action.label)}
              id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
            />
          )}

          {Object.keys(groupActions).map((group) => [
            <MenuItem key={toKey(group)} header={true}>{group}</MenuItem>,
            ...groupActions[group].map((action) =>
              <MenuAction
                {...action}
                key={action.id || action.name || toKey(action.label)}
                id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
              />
            )
          ])}

          {((0 !== unclassifiedActions.length || 0 !== Object.keys(groupActions).length) && 0 !== dangerousActions.length) &&
            <MenuItem divider={true} />
          }

          {dangerousActions.map((action) =>
            <MenuAction
              {...action}
              key={action.id || action.name || toKey(action.label)}
              id={action.id || action.name || `${props.id || props.name}-${toKey(action.label)}`}
            />
          )}
        </Menu>
      )
    }

    return (
      <RootCloseWrapper
        disabled={!props.open}
        onRootClose={this.handleRootClose}
        event={props.rootCloseEvent}
      >
        {props.menu}
      </RootCloseWrapper>
    )
  }
}

StandardMenu.propTypes = {
  id: T.string.isRequired,
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
  rootCloseEvent: T.oneOf(['click', 'mousedown'])
}

/**
 * Menu button.
 * Renders a component that will open a menu with additional actions.
 *
 * @param props
 * @constructor
 */
const MenuButton = props => {
  const isStandard = typeof props.menu === 'object' && props.menu.items
  let hasActions = false
  if (isStandard) {
    // check there is actions in the menu
    hasActions = !!props.menu.items.find(
      action => undefined === action.displayed || action.displayed
    )
  }

  // only display button if there are actions
  return (
    <MenuOverlay
      id={props.id}
      open={props.opened}
      position={props.menu.position}
      align={props.menu.align}
      className={props.containerClassName}
      disabled={(isStandard && !hasActions) || props.disabled}
      onToggle={props.onToggle}
    >
      <CallbackButton
        {...omit(props, 'menu', 'containerClassName', 'onToggle', 'opened')}
        className={classes('dropdown-toggle', props.className)}
        bsRole="toggle"
        callback={identity}
      >
        {props.children}
      </CallbackButton>

      <StandardMenu
        bsRole="menu"
        id={props.id}
        menu={props.menu}
      />
    </MenuOverlay>
  )
}

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
      position: T.oneOf(['top', 'bottom']),
      align: T.oneOf(['left', 'right']),
      items: T.arrayOf(T.shape(
        ActionTypes.propTypes
      )).isRequired
    })
  ]).isRequired
})

export {
  MenuButton
}
