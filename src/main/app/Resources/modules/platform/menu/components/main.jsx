import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import CloseButton from 'react-bootstrap/CloseButton'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'
import {Thumbnail} from '#/main/app/components/thumbnail'

import {PlatformMenuUser} from '#/main/app/platform/menu/components/user'

import {Menu} from '#/main/app/overlays/menu'
import {ContextHistory} from '#/main/app/context/components/history'
import {PlatformMenuQuickAccess} from '#/main/app/platform/menu/components/quick-access'
import {PlatformMenuHelp} from '#/main/app/platform/menu/components/help'
import {PlatformMenuGlobal} from '#/main/app/platform/menu/components/global'
import {constants, useSize} from '#/main/app/dom/size'

const AllContexts = () => {
  return (
    <Menu className="app-contexts-menu flyout-menu position-fixed p-0">
      <nav className="d-flex flex-row border-bottom align-items-center">
        <ul className="nav nav-underline ps-4 justify-content-around fs-sm me-auto">
          <li className="nav-item">
            <Button
              type={CALLBACK_BUTTON}
              className="nav-link active"
              label={trans('Favoris')}
              callback={() => true}
            />
          </li>
          <li className="nav-item">
            <Button
              type={CALLBACK_BUTTON}
              className="nav-link"
              label={trans('Récent')}
              callback={() => true}
            />
          </li>
          <li className="nav-item">
            <Button
              type={CALLBACK_BUTTON}
              className="nav-link"
              label={trans('Tous')}
              callback={() => true}
            />
          </li>
        </ul>

        <div className="flyout-menu-close fs-sm rounded-pill bg-body my-0" role="presentation">
          <CloseButton onClick={() => true} className="rounded-pill" aria-label={trans('close', {}, 'actions')} />
        </div>
      </nav>

      <div className="flyout-menu-content" role="presentation">
        <ContextHistory
          className="mb-0"
          /*onOpen={props.fadeModal}*/
          size="sm"
          flush={true}
        />
      </div>
    </Menu>
  )
}

const PlatformMenu = (props) => {
  const size = useSize()
  const vertical = ![constants.SIZE_XS, constants.SIZE_SM].includes(size)

  let pinnedContexts = [].concat(props.favoriteContexts)
  if (!isEmpty(props.currentContext) && 'workspace' === props.currentContextType) {
    let currentPos = pinnedContexts.findIndex((context) => context.id === props.currentContext.id)
    if (-1 === currentPos) {
      pinnedContexts.unshift(props.currentContext)
    }
  }

  const navTitleId = useId()

  const workspaceTitleId = useId()
  const workspaceDescId = useId()
  const accountTitleId = useId()
  const accountDescId = useId()

  return (
    <nav
      className={classes('app-main-menu gap-3', {
        'app-main-menu-h': !vertical,
        'app-main-menu-v': vertical
      })}
      aria-labelledby={navTitleId}
    >
      <h1 id={navTitleId} className="visually-hidden">{trans('main_menu')}</h1>

      <PlatformMenuQuickAccess vertical={vertical} />
      <PlatformMenuGlobal vertical={vertical} />

      {0 !== pinnedContexts.length &&
        <>
          <h2 id={workspaceTitleId} className="visually-hidden">{trans('my_workspaces')}</h2>
          <p id={workspaceDescId} className="visually-hidden">{trans('my_workspaces_help')}</p>
          <ul
            className="app-main-menu-group app-main-menu-contexts list-unstyled d-flex gap-2 flex-fill"
            aria-labelledby={workspaceTitleId}
            aria-describedby={workspaceDescId}
          >
            {pinnedContexts.map(pinnedContext => (
              <li key={pinnedContext.id || trans('loading')}>
                <Button
                  type={LINK_BUTTON}
                  className="app-context-btn position-relative focus-ring"
                  label={pinnedContext.name || trans('loading')}
                  tooltip={vertical ? 'right' : 'top'}
                  target={route(pinnedContext)}
                >
                  <Thumbnail
                    size="sm"
                    thumbnail={pinnedContext.thumbnail}
                    name={pinnedContext.name}
                    square={true}
                  />
                </Button>
              </li>
            ))}

            {false && <li>
              <Button
                type={MENU_BUTTON}
                className="app-context-btn focus-ring"
                icon="fa fa-ellipsis-h"
                label={trans('Plus d\'espaces')}
                tooltip="right"
                menu={{
                  drop: 'end',
                  align: 'end',
                  render: () => (
                    <AllContexts />
                  )
                }}
              />
            </li>}
          </ul>
        </>
      }

      <hr className={classes('app-main-menu-separator m-0', vertical ? 'mt-auto' : 'ms-auto')} aria-hidden={true} />

      <h2 id={accountTitleId} className="visually-hidden">{trans('account_links')}</h2>
      <p id={accountDescId} className="visually-hidden">{trans('account_links_help')}</p>
      <ul
        className="app-main-menu-group list-unstyled d-flex gap-2"
        aria-labelledby={accountTitleId}
        aria-describedby={accountDescId}
      >
        <li>
          <PlatformMenuUser vertical={vertical} />
        </li>

        <li>
          <PlatformMenuHelp vertical={vertical} />
        </li>
      </ul>
    </nav>
  )
}

PlatformMenu.propTypes = {
  currentContext: T.shape({
    id: T.string
  }),
  currentContextType: T.string,

  availableContexts: T.arrayOf(T.shape({

  })),
  favoriteContexts: T.arrayOf(T.shape({
    id: T.string.isRequired,
    slug: T.string.isRequired,
    name: T.string.isRequired,
    thumbnail: T.string
  }))
}

export {
  PlatformMenu
}
