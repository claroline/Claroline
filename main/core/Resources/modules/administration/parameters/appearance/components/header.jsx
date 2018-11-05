import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/administration/parameters/appearance/store/selectors'

const HeaderComponent = () =>
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
        icon: 'fa fa-fw fa-header',
        title: trans('header'),
        defaultOpened: true,
        fields: [
          {
            name: 'display.name_active',
            type: 'boolean',
            label: trans('show_name_in_top_bar'),
            required: false
          },
          {
            name: 'display.header_locale',
            type: 'boolean',
            label: trans('header_locale'),
            required: false
          },
          {
            name: 'display.logo',
            type: 'file',
            label: trans('logo'),
            required: false
          },
          {
            name: 'display.logo_redirect_home',
            type: 'boolean',
            label: trans('logo_redirect_home'),
            required: false
          }
          /*{
            name: 'display.theme',
            type: 'choice',
            label: trans('theme'),
            required: true,
            options: {
              multiple: false,
              condensed: true,
              choices: props.themeChoices
            }
          }*/
        ]
      }
    ]}
  />


HeaderComponent.propTypes = {
  themeChoices: T.object.isRequired
}

const Header = connect(
  (state) => ({
    themeChoices: selectors.themeChoices(state)
  }),
  () => ({ })
)(HeaderComponent)

export {
  Header
}
