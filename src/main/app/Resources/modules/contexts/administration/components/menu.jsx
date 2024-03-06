import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {ContextMenu} from '#/main/app/context/containers/menu'

import {MODAL_MAINTENANCE} from '#/main/app/modals/maintenance'
import {MODAL_PLATFORM_ABOUT} from '#/main/app/contexts/administration/modals/about'

const PlatformStatus = (props) =>
  <section className="app-menu-status">
    <h2 className="sr-only">
      {trans('status')}
    </h2>

    <LiquidGauge
      id="platform-status"
      type={classes({
        success: !props.disabled && !props.maintenance,
        warning: !props.disabled && props.maintenance,
        danger: props.disabled
      })}
      value={50}
      displayValue={() => {
        if (props.disabled) {
          return <tspan className="fa fa-power-off">&#xf011;</tspan>
        }

        if (props.maintenance) {
          return <tspan className="fa fa-hard-hat">&#xf807;</tspan>
        }

        return <tspan className="fa fa-check">&#xf00c;</tspan>
      }}
      width={70}
      height={70}
      preFilled={true}
    />

    <div className="app-menu-status-info">
      <h3 className="h5">
        {props.disabled && trans('platform_offline', {}, 'administration')}
        {!props.disabled && !props.maintenance && trans('platform_online', {}, 'administration')}
        {!props.disabled && props.maintenance && trans('platform_maintenance', {}, 'administration')}
      </h3>

      {props.disabled && trans('platform_disabled', {}, 'administration')}
      {!props.disabled && !props.maintenance && trans('platform_opened', {}, 'administration')}
      {!props.disabled && props.maintenance && trans('platform_active_admin', {}, 'administration')}
    </div>
  </section>

PlatformStatus.propTypes = {
  disabled: T.bool.isRequired,
  maintenance: T.bool.isRequired
}

const AdministrationMenu = props =>
  <ContextMenu
    basePath={props.basePath}
    title={trans('administration')}
    tools={props.tools}
    shortcuts={props.shortcuts}
    actions={[
      {
        name: 'about',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-circle-info',
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
      }
    ]}
  >
    {/*<PlatformStatus
      disabled={props.disabled}
      maintenance={props.maintenance}
    />*/}
  </ContextMenu>

AdministrationMenu.propTypes = {
  basePath: T.string.isRequired,
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  disabled: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  enableMaintenance: T.func.isRequired,
  disableMaintenance: T.func.isRequired
}

export {
  AdministrationMenu
}
