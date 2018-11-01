import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const PortalComponent = (props) =>
  <div>
    <FormData
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
          title: trans('portal'),
          defaultOpened: true,
          fields: [
            {
              name: 'portal.enabled_resources',
              type: 'choice',
              label: trans('portal_resources_configuration'),
              required: false,
              options: {
                choices: props.portalResources,
                multiple: true,
                condensed: false,
                inline: false
              }
            }
          ]
        }
      ]}
    />
  </div>

PortalComponent.propTypes = {
  portalResources: T.object.isRequired
}

const Portal = connect(
  state => ({
    portalResources: state.portalResources
  }),
  null
)(PortalComponent)

export {
  Portal
}
