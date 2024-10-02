import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector, useDispatch} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Action, PromisedAction} from '#/main/app/action/prop-types'

import {actions as platformActions, selectors as platformSelectors} from '#/main/app/platform/store'
import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'
import {PageBreadcrumb} from '#/main/app/page/components/breadcrumb'

const FavouriteButton = () => {
  const dispatch = useDispatch()

  const contextType = useSelector(contextSelectors.type)
  if ('workspace' !== contextType) {
    return null;
  }

  const contextData = useSelector(contextSelectors.data)
  let favourite = useSelector((state) => platformSelectors.isContextFavorite(state, contextData))

  return (
    <Button
      id="toggle-favorite"
      type={CALLBACK_BUTTON}
      label={trans(favourite ? 'remove-favourite' : 'add-favourite', {}, 'actions')}
      icon={classes('fa', {
        'fa-star text-warning': favourite,
        'far fa-star': !favourite
      })}
      tooltip="bottom"
      callback={() => dispatch(platformActions.saveFavorite(contextData))}
    />
  )
}

const MenuButton = () => {
  const dispatch = useDispatch()
  const menuOpened = useSelector(contextSelectors.menuOpened)

  return (
    <Button
      id="toggle-menu"
      type={CALLBACK_BUTTON}
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
    <div className={classes('app-page-menu px-4 py-2 py-lg-3 d-flex gap-4 flex-nowrap align-items-center bg-body', {
      'sticky-top': !props.embedded
    })} role="presentation">
      {!props.embedded &&
        <MenuButton />
      }

      {!props.embedded &&
        <FavouriteButton />
      }

      {!props.embedded &&
        <div className="text-truncate" role="presentation">
          <div role="presentation" className="d-flex align-items-center">
            <Button
              {...main}
              className="text-truncate text-reset h6 d-block m-0"
              type={LINK_BUTTON}
              style={{fontWeight: 500}}
            />
          </div>
          <PageBreadcrumb
            breadcrumb={breadcrumb}
            current={props.title}
          />
        </div>
      }

      {(1 < displayedNav.length || props.actions) &&
        <div className="ms-auto d-flex flex-nowrap me-n3">
          {1 < displayedNav.length &&
            <nav className="text-nowrap">
              <ul className="nav nav-pills flex-nowrap">
                {displayedNav.map((nav) =>
                  <li className="nav-item" key={nav.name || toKey(nav.label)}>
                    <Button
                      {...nav}
                      className="nav-link py-2 fw-normal"
                    />
                  </li>
                )}
              </ul>
            </nav>
          }

          {props.actions &&
            <Toolbar
              className={classes('nav nav-pills flex-nowrap', 1 >= displayedNav.length && 'ms-auto')}
              buttonName="nav-link py-2 fw-normal"
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
