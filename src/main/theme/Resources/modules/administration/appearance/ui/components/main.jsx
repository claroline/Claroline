import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {getMenus} from '#/main/app/layout/header/utils'
import {selectors} from '#/main/core/administration/parameters/store/selectors'

class UiMain extends Component {
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
          target: this.props.path,
          exact: true
        }}
        locked={this.props.lockedParameters}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'display.resource_icon_set',
                type: 'choice',
                label: trans('icons'),
                required: true,
                options: {
                  multiple: false,
                  condensed: true,
                  choices: this.props.iconSetChoices
                }
              }, {
                name: 'display.breadcrumb',
                type: 'boolean',
                label: trans('showBreadcrumbs')
              }
            ]
          }, {
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
                displayed: false, // implement new format
                options: {
                  inline: false,
                  multiple: true,
                  condensed: false,
                  choices: this.state.headerWidgets
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: trans('footer', {}, 'appearance'),
            fields: [
              {
                name: 'footer.show_terms_of_service',
                type: 'boolean',
                label: trans('footer_show_terms_of_service', {}, 'appearance')
              }, {
                name: 'footer.show_help',
                type: 'boolean',
                label: trans('footer_show_help', {}, 'appearance')
              }, {
                name: 'footer.show_locale',
                type: 'boolean',
                label: trans('footer_show_locale', {}, 'appearance')
              }, {
                name: 'footer.content',
                type: 'html',
                label: trans('footer', {}, 'appearance')
              }
            ]
          }
        ]}
      />
    )
  }
}

UiMain.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string).isRequired,
  iconSetChoices: T.object.isRequired
}

export {
  UiMain
}