import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector, useDispatch} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Action, PromisedAction} from '#/main/app/action/prop-types'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'

const MenuButton = () => {
  const dispatch = useDispatch()
  const menuOpened = useSelector(contextSelectors.menuOpened)

  return (
    <Button
      id="toggle-menu"
      type={CALLBACK_BUTTON}
      className="app-menu-toggle position-relative"
      label={trans(menuOpened ? 'hide-menu' : 'show-menu', {}, 'actions')}
      icon="fa fa-fw fa-bars"
      tooltip="bottom"
      callback={() => dispatch(contextActions.toggleMenu())}
    />
  )
}

const PageMenu = (props) => {
  const displayedNav = props.nav
    .filter(action => undefined === action.displayed || action.displayed)

  return (
    <div className="mx-4 my-3 d-flex gap-3 flex-nowrap align-items-center" role="presentation">
      {!props.embedded &&
        <MenuButton />
      }

      {1 < displayedNav.length &&
        <nav className="page-nav ms-auto">
          <ul className="nav nav-underline flex-nowrap">
            {displayedNav.map((nav) =>
              <li className="nav-item" key={nav.name || toKey(nav.label)}>
                <Button
                  {...nav}
                  className="nav-link"
                />
              </li>
            )}
          </ul>
        </nav>
      }

      {props.actions &&
        <Toolbar
          className={classes('nav nav-underline flex-nowrap', 1 >= displayedNav.length && 'ms-auto')}
          buttonName="nav-link"
          toolbar={props.toolbar}
          tooltip="bottom"
          actions={props.actions}
        />
      }
    </div>
  )
}

PageMenu.propTypes = {
  embedded: T.bool.isRequired,

  /**
   * The main navigation elements.
   */
  nav: T.arrayOf(T.shape(
    Action.propTypes
  )),
  toolbar: T.string,

  /**
   * A list of actions.
   */
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      Action.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedAction.propTypes
    )
  ])
}

PageMenu.defaultProps = {
  nav: [],
  toolbar: 'more'
}

export {
  PageMenu
}
