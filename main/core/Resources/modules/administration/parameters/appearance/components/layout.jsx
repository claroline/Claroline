import React, {Component} from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/appearance/store/selectors'
import {getMenus} from '#/main/app/layout/header/utils'

class Layout extends Component {
  constructor(props) {
    super(props)

    this.state = {
      headerWidgets: {}
    }
  }

  componentDidMount() {
    getMenus().then(menus => {
      this.setState({headerWidgets: menus.reduce((acc, current) => Object.assign(acc, {
        [current.default.name]: current.default.label
      }), {})})
    })
  }

  render() {
    return (
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
                name: 'display.logo',
                type: 'image',
                label: trans('logo')
              }, {
                name: 'display.name_active',
                type: 'boolean',
                label: trans('show_name_in_top_bar')
              }, {
                name: 'header',
                type: 'choice',
                label: trans('header_widgets'),
                options: {
                  inline: false,
                  multiple: true,
                  condensed: false,
                  choices: this.state.headerWidgets
                }
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
                name: 'footer.show_locale',
                type: 'boolean',
                label: trans('footer_locale')
              }, {
                name: 'footer.content',
                type: 'html',
                label: trans('footer')
              }
            ]
          }
        ]}
      />
    )
  }
}


export {
  Layout
}
