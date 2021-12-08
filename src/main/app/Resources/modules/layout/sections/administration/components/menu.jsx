import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {MODAL_MAINTENANCE} from '#/main/app/modals/maintenance'
import {MODAL_PLATFORM_ABOUT} from '#/main/app/layout/sections/administration/modals/about'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'

class PlatformStatus extends Component {
  constructor(props) {
    super(props)

    this.state = {
      mode: 'status'
    }
  }

  changeMode(mode) {
    this.setState({mode: mode})
  }

  render() {
    if ('usages' === this.state.mode) {
      return (
        <section className="app-menu-status">
          <h2 className="sr-only">
            {trans('usages')}
          </h2>

          <div>
            {trans('users')}
            <ProgressBar
              className="progress-minimal"
              value={25}
              size="xs"
              type="user"
            />
          </div>

          <div>
            {trans('storage')}

            <ProgressBar
              className="progress-minimal"
              value={25}
              size="xs"
              type="user"
            />
          </div>
        </section>
      )
    }

    return (
      <section className="app-menu-status">
        <h2 className="sr-only">
          {trans('status')}
        </h2>

        <LiquidGauge
          id="platform-status"
          type={classes({
            success: !this.props.disabled && !this.props.maintenance,
            warning: !this.props.disabled && this.props.maintenance,
            danger: this.props.disabled
          })}
          value={50}
          displayValue={() => {
            if (this.props.disabled) {
              return <tspan className="fa fa-power-off">&#xf011;</tspan>
            }

            if (this.props.maintenance) {
              return <tspan className="fa fa-hard-hat">&#xf807;</tspan>
            }

            return <tspan className="fa fa-check">&#xf00c;</tspan>
          }}
          width={70}
          height={70}
          preFilled={true}
        />

        <div className="app-menu-status-info">
          <h3 className="h4">
            {this.props.disabled && trans('platform_offline', {}, 'administration')}
            {!this.props.disabled && !this.props.maintenance && trans('platform_online', {}, 'administration')}
            {!this.props.disabled && this.props.maintenance && trans('platform_maintenance', {}, 'administration')}

            {false &&
              <Button
                className="icon-with-text-left"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-info-circle"
                label={trans('show-info', {}, 'actions')}
                callback={() => this.changeMode('usages' === this.state.mode ? 'status' : 'usages')}
                tooltip="right"
              />
            }
          </h3>

          {this.props.disabled && 'La plateforme est désactivée et n\'est plus accessible.'}
          {!this.props.disabled && !this.props.maintenance && 'La plateforme est ouverte aux utilisateurs.'}
          {!this.props.disabled && this.props.maintenance && 'La plateforme est fermée et uniquement accessible aux administrateurs.'}
        </div>
      </section>
    )
  }
}

PlatformStatus.propTypes = {
  disabled: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  usages: T.shape({
    users: T.number,
    storage: T.number
  }),
  restrictions: T.shape({
    users: T.number,
    storage: T.number,
    dates: T.arrayOf(T.string)
  })
}

const AdministrationMenu = props =>
  <MenuMain
    title={trans('administration')}
    backAction={{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-angle-double-left',
      label: trans('desktop'),
      target: '/desktop',
      exact: true
    }}

    tools={props.tools.map(tool => ({
      name: tool.name,
      icon: tool.icon,
      path: `/admin/${tool.name}`
    }))}
    actions={[
      {
        name: 'about',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-info',
        label: trans('show-info', {}, 'actions'),
        modal: [MODAL_PLATFORM_ABOUT]
      }, {
        name: 'enable-maintenance',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-hard-hat',
        label: trans('enable-maintenance', {}, 'actions'),
        modal: [MODAL_MAINTENANCE, {
          handleConfirm: (message) => props.enableMaintenance(message)
        }],
        displayed: !props.maintenance,
        group: trans('management')
      }, {
        name: 'disable-maintenance',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-hard-hat',
        label: trans('disable-maintenance', {}, 'actions'),
        callback: () => props.disableMaintenance(),
        confirm: {
          title: trans('maintenance_mode', {}, 'administration'),
          subtitle: trans('deactivation'),
          message: trans('maintenance_mode_deactivation', {}, 'administration'),
          button: trans('disable', {}, 'actions')
        },
        displayed: props.maintenance,
        group: trans('management')
      }, {
        name: 'disable',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-ban',
        label: trans('disable', {}, 'actions'),
        callback: () => true,
        dangerous: true,
        group: trans('management'),
        displayed: false
      }
    ]}
  >
    <PlatformStatus
      disabled={props.disabled}
      maintenance={props.maintenance}
    />

    <ToolMenu
      opened={'tool' === props.section}
      toggle={() => props.changeSection('tool')}
    />
  </MenuMain>

AdministrationMenu.propTypes = {
  section: T.string,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired
  })),
  changeSection: T.func.isRequired,

  usages: T.shape({
    users: T.number,
    storage: T.number
  }),
  restrictions: T.shape({
    users: T.number,
    storage: T.number,
    dates: T.arrayOf(T.string)
  }),

  disabled: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  enableMaintenance: T.func.isRequired,
  disableMaintenance: T.func.isRequired
}

AdministrationMenu.defaultProps = {
  tools: []
}

export {
  AdministrationMenu
}
