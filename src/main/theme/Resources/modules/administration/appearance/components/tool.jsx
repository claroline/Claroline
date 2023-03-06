import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {ASYNC_BUTTON, DOWNLOAD_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {getMenus} from '#/main/app/layout/header/utils'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {AppearanceIcons} from '#/main/theme/administration/appearance/containers/icons'

import {MODAL_ICON_SET_CREATION} from '#/main/theme/administration/appearance/modals/icon-set-creation'

class AppearanceTool extends Component {
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
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
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
            icon: 'fa fa-fw fa-swatchbook',
            title: trans('theme', {}, 'appearance'),
            fields: [
              {
                name: 'display.theme',
                type: 'choice',
                label: trans('theme', {}, 'appearance'),
                required: true,
                hideLabel: true,
                options: {
                  multiple: false,
                  condensed: false,
                  noEmpty: true,
                  choices: this.props.availableThemes
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-icons',
            title: trans('icons', {}, 'appearance'),
            actions: [
              {
                name: 'add',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_icon_set', {}, 'actions'),
                modal: [MODAL_ICON_SET_CREATION, {
                  onSave: this.props.addIconSet
                }]
              }
            ],
            fields: [
              {
                name: 'display.resource_icon_set',
                type: 'choice',
                label: trans('icons', {}, 'appearance'),
                required: true,
                hideLabel: true,
                options: {
                  multiple: false,
                  condensed: false,
                  noEmpty: true,
                  choices: []
                    .concat(this.props.availableIconSets)
                    .sort((a, b) => {
                      if (a.default || a.name < b.name) {
                        return 1
                      }

                      return -1
                    })
                    .reduce((acc, current) => Object.assign({
                      [current.name]: (
                        <Fragment key={current.name}>
                          {current.name}
                          {current.default &&
                            <small> &nbsp;({trans('default')})</small>
                          }

                          <Toolbar
                            style={{marginLeft: 'auto'}}
                            buttonName="btn btn-link btn-sm"
                            tooltip="bottom"
                            actions={[
                              {
                                name: 'export',
                                type: DOWNLOAD_BUTTON,
                                icon: 'fa fa-fw fa-download',
                                label: trans('download', {}, 'actions'),
                                file: {url: ['apiv2_icon_set_get', {iconSet: current.name}]}
                              }, {
                                name: 'delete',
                                type: ASYNC_BUTTON,
                                icon: 'fa fa-fw fa-trash',
                                label: trans('delete', {}, 'actions'),
                                request: {
                                  url: ['apiv2_icon_set_delete', {iconSet: current.name}],
                                  request: {
                                    method: 'DELETE'
                                  },
                                  success: () => this.props.removeIconSet(current)
                                },
                                disabled: current.default,
                                confirm: {
                                  title: transChoice('icon_set_delete_confirm_title', 1, {}, 'appearance'),
                                  subtitle: current.name,
                                  message: transChoice('icon_set_delete_confirm_message', 1, {count: 1}, 'appearance')
                                },
                                dangerous: true
                              }
                            ]}
                          />
                        </Fragment>
                      )
                    }, acc), {})
                }
              }
            ],
            component: AppearanceIcons
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: trans('footer', {}, 'appearance'),
            fields: [
              {
                name: 'footer.content',
                type: 'html',
                label: trans('footer', {}, 'appearance')
              }, {
                name: 'footer.show',
                type: 'boolean',
                label: trans('footer_show', {}, 'appearance'),
                linked: [
                  {
                    name: 'footer.show_terms_of_service',
                    type: 'boolean',
                    label: trans('footer_show_terms_of_service', {}, 'appearance'),
                    displayed: (params) => get(params, 'footer.show', false)
                  }, {
                    name: 'footer.show_help',
                    type: 'boolean',
                    label: trans('footer_show_help', {}, 'appearance'),
                    displayed: (params) => get(params, 'footer.show', false)
                  }, {
                    name: 'footer.show_locale',
                    type: 'boolean',
                    label: trans('footer_show_locale', {}, 'appearance'),
                    displayed: (params) => get(params, 'footer.show', false)
                  }
                ]
              }
            ]
          }
        ]}
      />
    )
  }
}

AppearanceTool.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string).isRequired,
  availableThemes: T.object,
  availableIconSets: T.array.isRequired,

  addIconSet: T.func.isRequired,
  removeIconSet: T.func.isRequired
}

export {
  AppearanceTool
}
