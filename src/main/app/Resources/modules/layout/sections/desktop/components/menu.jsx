import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans, number} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security/permissions'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {route as toolRoute} from '#/main/core/tool/routing'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {getActions} from '#/main/core/desktop'
import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const DesktopShortcuts = props =>
  <Toolbar
    id="app-menu-shortcuts"
    className="app-menu-shortcuts"
    buttonName="btn btn-link"
    tooltip="bottom"
    actions={props.shortcuts}
    onClick={props.autoClose}
  />

const DesktopProgression = () =>
  <section className="app-menu-status">
    <h2 className="sr-only">
      {trans('my_progression')}
    </h2>

    <LiquidGauge
      id="desktop-progression"
      type="user"
      value={25}
      displayValue={(value) => number(value) + '%'}
      width={70}
      height={70}
    />

    <div className="app-menu-status-info">
      {trans('Vous n\'avez pas terminé toutes les activités disponibles.')}
    </div>
  </section>

const DesktopMenu = props => {
  const desktopActions = getActions(props.currentUser)

  return (
    <MenuMain
      title={trans('desktop')}
      backAction={{
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-angle-double-left',
        label: trans('home'),
        target: '/',
        exact: true
      }}
      tools={props.tools
        .filter((tool) => hasPermission('open', tool))
        .map(tool => ({
          name: tool.name,
          icon: tool.icon,
          path: toolRoute(tool.name),
          order: get(tool, 'display.order'),
          displayed: !get(tool, 'restrictions.hidden', false)
        }))
      }
      actions={desktopActions}
    >
      {props.showProgression &&
        <DesktopProgression />
      }

      {!isEmpty(props.shortcuts) &&
        <DesktopShortcuts
          shortcuts={desktopActions.then(actions => {
            return props.shortcuts
              .map(shortcut => {
                if ('tool' === shortcut.type) {
                  const tool = props.tools.find(tool => tool.name === shortcut.name)
                  if (tool) {
                    return {
                      name: shortcut.name,
                      type: LINK_BUTTON,
                      icon: `fa fa-fw fa-${tool.icon}`,
                      label: trans('open-tool', {tool: trans(tool.name, {}, 'tools')}, 'actions'),
                      target: toolRoute(tool.name)
                    }
                  }

                } else {
                  return actions.find(action => action.name === shortcut.name)
                }
              })
              .filter(link => !!link)
          })}
        />
      }

      <ToolMenu
        opened={'tool' === props.section}
        toggle={() => props.changeSection('tool')}
      />
    </MenuMain>
  )
}

DesktopMenu.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  showProgression: T.bool.isRequired,
  section: T.string,
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  changeSection: T.func.isRequired
}

DesktopMenu.defaultProps = {
  showProgression: false,
  shortcuts: [],
  tools: []
}

export {
  DesktopMenu
}
