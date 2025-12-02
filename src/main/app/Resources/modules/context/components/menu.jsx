import React, {useCallback, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {useLocaleStorage} from '#/main/app/storage'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, CallbackButton, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'
import {Menu} from '#/main/app/overlays/menu'

import {actions as platformActions} from '#/main/app/platform/store'
import {MODAL_PLATFORM_ORGANIZATIONS} from '#/main/app/platform/modals/organizations'

import {ContextFavourite} from '#/main/app/context/components/favorite'
import {actions, selectors} from '#/main/app/context/store'

const ContextFlyout = (props) => {
  const dispatch = useDispatch()

  // get context organizations
  const organizations = useSelector(selectors.organizations)
  // get context tools
  const toolLinks = useSelector(selectors.toolLinks)

  const menuTitleId = useId()
  const toolsTitleId = useId()
  const organizationsTitleId = useId()
  const organizationsDescId = useId()

  return (
    <Menu
      id={props.id}
      className="app-context-menu flyout-menu p-0 mt-2"
      role="navigation"
      aria-labelledby={menuTitleId}
    >
      <div className="flyout-menu-content" role="presentation">
        <h1 id={menuTitleId} className="visually-hidden">{trans('context_menu')}</h1>
        <div className="d-flex gap-3 px-4 pt-4 my-n1 align-items-center" role="presentation">
          <Button
            id="toggle-menu"
            type={CALLBACK_BUTTON}
            className="btn btn-text-body p-1 focus-ring"
            label={trans('pin-menu', {}, 'actions')}
            tooltip="bottom"
            callback={props.togglePin}
            size="sm"
          >
            <span className="fa fa-thumb-tack fs-base" aria-hidden={true} />
          </Button>

          <ContextFavourite />
        </div>

        {1 < toolLinks.length &&
          <div role="presentation">
            <h2 id={toolsTitleId} className="visually-hidden">{trans('tools')}</h2>
            <ul
              className={classes('flyout-menu-items list-unstyled p-4 mb-0', {
                'flyout-menu-items-2': 6 >= toolLinks.length,
                'flyout-menu-items-4': 6 < toolLinks.length
              })}
              aria-labelledby={toolsTitleId}
            >
              {toolLinks.map(toolLink =>
                <li key={toolLink.name}>
                  <Button
                    {...omit(toolLink, 'label')}
                    className="flyout-menu-item focus-ring"
                    onClick={props.closeMenu}
                  >
                    <span className="text-truncate w-100 text-center" role="presentation">{toolLink.label}</span>
                  </Button>
                </li>
              )}
            </ul>
          </div>
        }

        {1 < organizations.length &&
          <div className="bg-body-tertiary p-4 d-flex flex-column" role="presentation">
            <h2 id={organizationsTitleId} className="fs-sm text-body-secondary text-uppercase">
              {trans('organizations', {}, 'community')}
            </h2>
            <p id={organizationsDescId} className="visually-hidden">{trans('change_organization_help', {}, 'community')}</p>

            <ul
              className="list-unstyled d-flex flex-column gap-2 m-n1 mb-0"
              aria-labelledby={organizationsTitleId}
              aria-describedby={organizationsDescId}
            >
              {organizations.slice(0, 3).map(organization => (
                <li key={organization.id}>
                  <CallbackButton
                    className="fw-bolder btn btn-link text-reset p-1 w-100 fs-sm"
                    callback={() => dispatch(platformActions.changeOrganization(organization))}
                    onClick={props.closeMenu}
                  >
                    <DataMicro object={organization} />
                  </CallbackButton>
                </li>
              ))}
            </ul>

            {3 < organizations.length &&
              <Button
                className="btn btn-link ms-auto mt-3 mb-n1 me-n2"
                type={MODAL_BUTTON}
                label={trans('see_all', {}, 'actions')}
                modal={[MODAL_PLATFORM_ORGANIZATIONS, {
                  organizations: organizations
                }]}
                size="sm"
                onClick={props.closeMenu}
              >
                <span className="fa fa-arrow-right ms-2" aria-hidden={true} />
              </Button>
            }
          </div>
        }
      </div>
    </Menu>
  )
}

ContextFlyout.propTypes = {
  id: T.string.isRequired,
  path: T.string,
  contextData: T.object,
  contextType: T.string,
  togglePin: T.func.isRequired,
  closeMenu: T.func.isRequired
}

const ContextMenu = () => {
  const dispatch = useDispatch()

  const contextPath = useSelector(selectors.path)
  const notFound = useSelector(selectors.notFound)
  const hasErrors = useSelector(selectors.hasErrors)

  const menuId = useId()
  const menuOpened = useSelector(selectors.menuOpened)
  const toggleMenu = useCallback(() => {
    dispatch(actions.toggleMenuOpen())
  }, [contextPath])

  const [pinedMenu, setPinedMenu] = useLocaleStorage('contextMenuPined', false)

  if (!pinedMenu) {
    return (
      <Button
        id="toggle-menu"
        type={MENU_BUTTON}
        className="app-context-menu-toggle btn btn-text-body focus-ring py-1 px-2 mx-n2 my-2 rounded-1 border-0"
        icon="fa fa-bars"
        label={trans(menuOpened ? 'close_context_menu': 'show_context_menu', {}, 'actions')}
        tooltip="bottom"
        onToggle={toggleMenu}
        disabled={notFound || hasErrors}
        opened={menuOpened}
        aria-controls={menuId}
        menu={
          <ContextFlyout
            id={menuId}
            togglePin={() => {
              setPinedMenu(!pinedMenu)
              toggleMenu()
              setTimeout(() => {
                document.querySelector('.app-context-menu-toggle').focus()
              }, 0)
            }}
            closeMenu={toggleMenu}
          />
        }
      />
    )
  }

  return (
    <Button
      id="toggle-menu"
      type={CALLBACK_BUTTON}
      className="app-context-menu-toggle btn btn-text-body focus-ring py-1 px-2 mx-n2 my-2 rounded-1 border-0"
      icon="fa fa-square-caret-left"
      label={trans('close_context_menu', {}, 'actions')}
      tooltip="bottom"
      callback={() => {
        setPinedMenu(!pinedMenu)
        setTimeout(() => {
          document.querySelector('.app-context-menu-toggle').focus()
        }, 0)
      }}
      aria-expanded={true}
      aria-controls={menuId}
    />
  )
}

export {
  ContextMenu
}
