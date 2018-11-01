import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const MaintenanceComponent = () => {
  return(<FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('maintenance'),
        defaultOpened: true,
        fields: [
          {
            name: 'maintenance.enable',
            type: 'boolean',
            label: trans('enable'),
            required: false
          },
          {
            name: 'maintenance.message',
            type: 'html',
            label: trans('content'),
            required: false,
            options: {
              long: true
            }
          }
        ]
      }
    ]}
  />)
}

MaintenanceComponent.propTypes = {
  availablesLocales: T.object.isRequired
}

const Maintenance = connect(
  state => ({
    availablesLocales: state.availablesLocales
  }),
  null
)(MaintenanceComponent)

export {
  Maintenance
}
