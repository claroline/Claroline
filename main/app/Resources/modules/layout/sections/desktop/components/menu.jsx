import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans, number} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {route as toolRoute} from '#/main/core/tool/routing'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const DesktopMenu = props => {
  const actions = [
    {
      name: 'walkthrough',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-street-view',
      label: trans('show-walkthrough', {}, 'actions'),
      callback: () => true,
      subscript: {
        type: 'label',
        status: 'primary',
        value: 'coming soon'
      }
    }, {
      name: 'impersonation',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-mask',
      label: trans('view-as', {}, 'actions'),
      displayed: props.isAdmin,
      modal: [MODAL_USERS, {
        selectAction: (users) => ({
          type: URL_BUTTON,
          target: !isEmpty(users) ? url(['claro_index', {_switch: users[0].username}])+'#/desktop' : ''
        })
      }]
    }
  ]

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

      tools={props.tools.map(tool => ({
        name: tool.name,
        icon: tool.icon,
        path: toolRoute(tool.name)
      }))}
      actions={actions}
    >
      {props.showProgression &&
        <section className="app-menu-progression">
          <h2 className="sr-only">
            Ma progression
          </h2>

          <LiquidGauge
            id="desktop-progression"
            type="user"
            value={25}
            displayValue={(value) => number(value) + '%'}
            width={70}
            height={70}
          />

          <div className="app-menu-progression-info">
            {trans('Vous n\'avez pas terminé toutes les activités disponibles.')}
          </div>
        </section>
      }

      {!isEmpty(props.shortcuts) &&
        <Toolbar
          id="shortcuts-desktop"
          className="app-menu-shortcuts"
          buttonName="btn btn-link"
          tooltip="bottom"
          toolbar={props.shortcuts.join(' ')}
          actions={props.shortcuts
            .map(shortcut => {
              if ('tool' === shortcut.type) {
                const tool = props.tools.find(tool => tool.name === shortcut.name)
                if (tool) {
                  return {
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
            .filter(link => !!link)}
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
  isAdmin: T.bool.isRequired,
  showProgression: T.bool.isRequired,
  section: T.string,
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired
  })),
  changeSection: T.func.isRequired
}

DesktopMenu.defaultProps = {
  showProgression: false,
  tools: []
}

export {
  DesktopMenu
}
