import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/appearance/store/selectors'

const Layout = () =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-heading',
        title: trans('header'),
        fields: [
          {
            name: 'display.name_active',
            type: 'boolean',
            label: trans('show_name_in_top_bar')
          }, {
            name: 'display.header_locale',
            type: 'boolean',
            label: trans('header_locale')
          }, {
            name: 'display.logo',
            type: 'image',
            label: trans('logo')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-map-signs',
        title: trans('breadcrumb'),
        fields: [
          {
            name: 'display.breadcrumb',
            type: 'boolean',
            label: trans('showBreadcrumbs')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-copyright',
        title: trans('footer'),
        fields: [
          {
            name: 'display.footer_login',
            type: 'boolean',
            label: trans('show_connection_button_at_footer', {}, 'home')
          }, {
            name: 'display.footer_workspaces',
            type: 'boolean',
            label: trans('show_workspace_menu_at_footer', {}, 'home')
          }, {
            name: 'display.footer',
            type: 'html',
            label: trans('footer')
          }
        ]
      }
    ]}
  />

export {
  Layout
}
