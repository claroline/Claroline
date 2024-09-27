import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector, useDispatch} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Action, PromisedAction} from '#/main/app/action/prop-types'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'
import {PageBreadcrumb} from '#/main/app/page/components/breadcrumb'

const MenuButton = () => {
  const dispatch = useDispatch()
  const menuOpened = useSelector(contextSelectors.menuOpened)

  return (
    <Button
      id="toggle-menu"
      type={CALLBACK_BUTTON}
      className="app-menu-toggle position-relative"
      label={trans(menuOpened ? 'hide-menu' : 'show-menu', {}, 'actions')}
      icon="fa fa-bars"
      tooltip="bottom"
      callback={() => dispatch(contextActions.toggleMenu())}
    />
  )
}

const PageMenu = (props) => {
  const displayedNav = props.nav
    .filter(action => undefined === action.displayed || action.displayed)

  const breadcrumb = [].concat(props.breadcrumb)
  const main = breadcrumb.shift()

  return (
    <div className="app-page-menu px-4 py-3 d-flex gap-4 flex-nowrap align-items-center bg-body sticky-top" role="presentation">
      {!props.embedded &&
        <MenuButton />
      }

      {!props.embedded &&
        <div className="" role="presentation">
          <Button
            {...main}
            className="text-reset h6"
            type={LINK_BUTTON}
            style={{fontWeight: 500}}
          />
          <PageBreadcrumb
            breadcrumb={breadcrumb}
            current={props.title}
          />
        </div>
      }

      {(1 < displayedNav.length || props.actions) &&
        <div className="ms-auto d-flex flex-nowrap me-n3">
          {1 < displayedNav.length &&
            <nav className="page-nav">
              <ul className="nav nav-pills flex-nowrap">
                {displayedNav.map((nav) =>
                  <li className="nav-item" key={nav.name || toKey(nav.label)}>
                    <Button
                      {...nav}
                      className="nav-link py-2"
                    />
                  </li>
                )}
              </ul>
            </nav>
          }

          {props.actions &&
            <Toolbar
              className={classes('nav nav-pills flex-nowrap', 1 >= displayedNav.length && 'ms-auto')}
              buttonName="nav-link py-2"
              toolbar={props.toolbar}
              tooltip="bottom"
              actions={props.actions}
            />
          }
        </div>
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
