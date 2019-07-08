import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const SecurityComponent = props =>
  <FormData
    name={selectors.FORM_NAME}
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
        title: trans('pdf'),
        defaultOpened: true,
        fields: [
          {
            name: 'security.platform_init_date',
            type: 'date',
            label: trans('platform_init_date'),
            required: false
          },
          {
            name: 'security.platform_limit_date',
            type: 'date',
            label: trans('platform_expiration_date'),
            required: false
          },
          {
            name: 'security.default_root_anon_id',
            type: 'string',
            label: trans('default_admin'),
            required: false
          },
          {
            name: 'security.disabled_admin_tools',
            type: 'choice',
            label: trans('disabled_admin_tools'),
            required: false,
            options: {
              choices: props.toolChoices,
              multiple: true,
              condensed: false,
              inline: false
            }
          }
        ]
      }
    ]}
  />

SecurityComponent.propTypes = {
  toolChoices: T.object.isRequired
}

const Security = connect(
  (state) => ({
    toolChoices: selectors.toolChoices(state)
  })
)(SecurityComponent)

export {
  Security
}
