import React from 'react'
import {connect} from 'react-redux'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

const IdentificationComponent = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/identification',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('Identification'),
        defaultOpened: true,
        fields: [
          {
            name: 'display.name',
            type: 'string',
            label: trans('name'),
            required: false
          },
          {
            name: 'display.secondary_name',
            type: 'string',
            label: trans('secondary_name'),
            required: false,
            options: {
              long: false
            }
          }
        ]
      }
    ]}
  />


IdentificationComponent.propTypes = {
}

const Identification = connect(
  null,
  () => ({ })
)(IdentificationComponent)

export {
  Identification
}
