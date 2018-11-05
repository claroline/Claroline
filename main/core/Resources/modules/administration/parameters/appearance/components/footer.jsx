import React from 'react'
import {connect} from 'react-redux'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

const FooterComponent = () =>
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
        icon: 'fa fa-fw fa-copyright',
        title: trans('footer'),
        defaultOpened: true,
        fields: [
          {
            name: 'display.footer',
            type: 'string',
            label: trans('footer'),
            required: false
          },
          {
            name: 'display.footer_login',
            type: 'boolean',
            label: trans('show_connection_button_at_footer', {}, 'home'),
            required: false
          },
          {
            name: 'display.footer_workspaces',
            type: 'boolean',
            label: trans('show_workspace_menu_at_footer', {}, 'home'),
            required: false
          }
        ]
      }
    ]}
  />


FooterComponent.propTypes = {
}

const Footer = connect(
  () => ({ }),
  () => ({ })
)(FooterComponent)

export {
  Footer
}
