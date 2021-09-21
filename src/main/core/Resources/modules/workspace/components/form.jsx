import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as workspaceSelectors} from '#/main/core/workspace/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {route} from '#/main/core/workspace/routing'

// todo : fix tool selection for opening

// easy selection for restrictions
const restrictByDates   = (workspace) => get(workspace, 'restrictions.enableDates') || !isEmpty(get(workspace, 'restrictions.dates'))
const restrictByCode    = (workspace) => get(workspace, 'restrictions.enableCode') || !!get(workspace, 'restrictions.code')
const restrictByIps     = (workspace) => get(workspace, 'restrictions.enableIps') || !isEmpty(get(workspace, 'restrictions.allowedIps'))

const WorkspaceFormComponent = (props) =>
  <FormData
    {...props}
    meta={true}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            required: true
          }, {
            name: 'extra.model',
            type: 'workspace',
            label: trans('create_from_model'),
            options: {
              picker: {
                model: true,
                title: trans('workspace_models')
              }
            },
            displayed: props.new,
            mode: 'standard'
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            type: 'string',
            label: trans('description'),
            options: {
              long: true
            },
            mode: 'standard'
          }, {
            name: 'contactEmail',
            label: trans('contact'),
            type: 'email',
            mode: 'standard'
          }, {
            name: 'meta.model',
            label: trans('define_as_model'),
            type: 'boolean',
            disabled: !props.new,
            mode: 'expert'
          }, {
            name: 'meta.personal',
            label: trans('personal'),
            type: 'boolean',
            disabled: true,
            mode: 'expert'
          }, {
            name: 'meta._forceLang',
            type: 'boolean',
            label: trans('default_language'),
            calculated: (workspace) => get(workspace, 'meta._forceLang') || get(workspace, 'meta.lang'),
            onChange: activated => {
              if (!activated) {
                // reset lang field
                props.updateProp('meta.lang', null)
              }
            },
            mode: 'advanced',
            linked: [{
              name: 'meta.lang',
              label: trans('lang'),
              type: 'locale',
              displayed: (workspace) => get(workspace, 'meta._forceLang') || get(workspace, 'meta.lang')
            }]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }, {
            name: 'display.showProgression',
            type: 'boolean',
            label: trans('showProgression'),
            mode: 'advanced'
          }, {
            name: 'breadcrumb.displayed',
            type: 'boolean',
            label: trans('showBreadcrumbs'),
            displayed: props.hasBreadcrumb, // only show breadcrumb config if it's not disabled at platform level
            mode: 'advanced',
            linked: [
              {
                name: 'breadcrumb.items',
                type: 'choice',
                label: trans('links'),
                required: true,
                displayed: (workspace) => get(workspace, 'breadcrumb.displayed') || false,
                options: {
                  choices: {
                    desktop: trans('desktop'),
                    workspaces: trans('workspace_list'),
                    current: trans('current_workspace'),
                    tool: trans('tool')
                  },
                  inline: false,
                  condensed: false,
                  multiple: true
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('opening_parameters'),
        mode: 'advanced',
        fields: [
          {
            name: 'opening.type',
            type: 'choice',
            label: trans('type'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                tool: trans('open_workspace_tool'),
                resource: trans('open_workspace_resource')
              }
            },
            onChange: () => props.updateProp('opening.target', null),
            linked: [
              {
                name: 'opening.target',
                type: 'choice',
                label: trans('tool'),
                required: true,
                displayed: (workspace) => workspace.opening && 'tool' === workspace.opening.type,
                options: {
                  noEmpty: true,
                  multiple: false,
                  condensed: true,
                  choices: props.tools ? props.tools.reduce((acc, tool) => Object.assign(acc, {
                    [tool.name]: trans(tool.name, {}, 'tools')
                  }), {}) : {}
                }
              }, {
                name: 'opening.target',
                type: 'resource',
                help: trans ('opening_target_resource_help'),
                label: trans('resource'),
                options: {
                  picker: {
                    current: props.root,
                    root: props.root
                  }
                },
                required: true,
                displayed: (workspace) => workspace.opening && 'resource' === workspace.opening.type,
                onChange: (selected) => {
                  props.updateProp('opening.target', selected)
                }
              }
            ]
          }, {
            name: 'opening.menu',
            type: 'choice',
            label: trans('tools_menu'),
            mode: 'expert',
            placeholder: trans('do_nothing'),
            options: {
              condensed: false,
              noEmpty: false,
              choices: {
                open: trans('open_tools_menu'),
                close: trans('close_tools_menu')
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('registration'),
        mode: 'standard',
        fields: [
          {
            name: 'registration.url',
            type: 'url',
            label: trans('registration_url'),
            calculated: (workspace) => url(['claro_index', {}, true])+`#${route(workspace)}`,
            required: true,
            disabled: true,
            displayed: !props.new
          }, {
            name: 'registration.selfRegistration',
            type: 'boolean',
            label: trans('activate_self_registration'),
            help: trans('self_registration_workspace_help'),
            linked: [
              {
                name: 'registration.validation',
                type: 'boolean',
                label: trans('validate_registration'),
                help: trans('validate_registration_help'),
                displayed: (workspace) => workspace.registration && workspace.registration.selfRegistration
              }
            ]
          }, {
            name: 'registration.defaultRole',
            type: 'role',
            label: trans('default_role'),
            displayed: !props.new,
            required: true,
            options: {
              picker: {
                url: ['apiv2_workspace_list_roles', {id: props.id}],
                filters: []
              }
            }
          }, {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: trans('activate_self_unregistration'),
            help: trans('self_unregistration_workspace_help')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        mode: 'advanced',
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help'),
            mode: 'expert'
          }, {
            name: 'restrictions.enableDates',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            calculated: restrictByDates,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.dates', [])
              }
            },
            displayed: props.isAdmin,
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: restrictByDates,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }, {
            name: 'restrictions.enableCode',
            label: trans('restrict_by_code'),
            type: 'boolean',
            calculated: restrictByCode,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.code', null)
              }
            },
            linked: [
              {
                name: 'restrictions.code',
                label: trans('access_code'),
                displayed: restrictByCode,
                type: 'password',
                required: true
              }
            ]
          }, {
            name: 'restrictions.enableIps',
            label: trans('restrict_by_ips'),
            type: 'boolean',
            calculated: restrictByIps,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.allowedIps', [])
              }
            },
            mode: 'expert',
            linked: [
              {
                name: 'restrictions.allowedIps',
                label: trans('allowed_ips'),
                type: 'collection',
                required: true,
                displayed: restrictByIps,
                options: {
                  type: 'ip',
                  placeholder: trans('no_allowed_ip'),
                  button: trans('add_ip')
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-bell-o',
        title: trans('notifications'),
        mode: 'advanced',
        fields: [
          {
            name: 'notifications.enabled',
            type: 'boolean',
            label: trans('enable_notifications')
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

WorkspaceFormComponent.propTypes = {
  tools: T.array,
  root: T.object,
  children: T.any,
  // from redux
  isAdmin: T.bool.isRequired,
  hasBreadcrumb: T.bool.isRequired,
  new: T.bool.isRequired,
  id: T.string,
  updateProp: T.func.isRequired
}

const WorkspaceForm = connect(
  (state, ownProps) => ({
    isAdmin: securitySelectors.isAdmin(state),
    hasBreadcrumb: configSelectors.param(state, 'display.breadcrumb'),
    new: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    id: formSelectors.data(formSelectors.form(state, ownProps.name)).id,
    tools: workspaceSelectors.tools(state),
    root: workspaceSelectors.root(state)
  }),
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(WorkspaceFormComponent)

export {
  WorkspaceForm
}
