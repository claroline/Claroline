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
  selectors as formSelect
} from '#/main/app/content/form/store'

import {route} from '#/main/core/workspace/routing'

// todo : fix tool selection for opening

// easy selection for restrictions
const restrictByDates   = (workspace) => get(workspace, 'restrictions.enableDates') || !isEmpty(get(workspace, 'restrictions.dates'))
const restrictByCode    = (workspace) => get(workspace, 'restrictions.enableCode') || !!get(workspace, 'restrictions.code')
const restrictByIps     = (workspace) => get(workspace, 'restrictions.enableIps') || !isEmpty(get(workspace, 'restrictions.allowedIps'))

const restrictResources = (workspace) => workspace.restrictions && (workspace.restrictions.enableMaxResources || 0 === workspace.restrictions.maxResources || !!workspace.restrictions.maxResources)
const restrictUsers     = (workspace) => workspace.restrictions && (workspace.restrictions.enableMaxUsers     || 0 === workspace.restrictions.maxUsers || !!workspace.restrictions.maxUsers)
const restrictStorage   = (workspace) => workspace.restrictions && (workspace.restrictions.enableMaxStorage   || !!workspace.restrictions.maxStorage)

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
            name: 'meta.forceLang',
            type: 'boolean',
            label: trans('default_language'),
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
              displayed: (workspace) => workspace.meta && workspace.meta.forceLang
            }]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }, {
            name: 'display.showMenu',
            type: 'boolean',
            label: trans('showTools'),
            mode: 'expert'
          }, {
            name: 'display.showProgression',
            type: 'boolean',
            label: trans('showProgression'),
            mode: 'advanced'
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-map-signs',
        title: trans('breadcrumb'),
        mode: 'advanced',
        displayed: props.hasBreadcrumb, // only show breadcrumb config if it's not disabled at platform level
        fields: [
          {
            name: 'breadcrumb.displayed',
            type: 'boolean',
            label: trans('showBreadcrumbs'),
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
          }, {
            name: 'restrictions.enableMaxUsers',
            type: 'boolean',
            label: trans('restrict_max_users'),
            calculated: restrictUsers,
            displayed: props.isAdmin,
            onChange: activated => {
              if (!activated) {
                // reset max users field
                props.updateProp('restrictions.maxUsers', null)
              }
            },
            linked: [
              {
                name: 'restrictions.maxUsers',
                type: 'number',
                label: trans('maxUsers'),
                displayed: restrictUsers,
                required: true,
                options: {
                  min: 0
                }
              }
            ]
          }, {
            name: 'restrictions.enableMaxResources',
            type: 'boolean',
            label: trans('restrict_max_resources'),
            calculated: restrictResources,
            displayed: props.isAdmin,
            onChange: activated => {
              if (!activated) {
                // reset max users field
                props.updateProp('restrictions.maxResources', null)
              }
            },
            linked: [
              {
                name: 'restrictions.maxResources',
                type: 'number',
                label: trans('max_amount_resources'),
                displayed: restrictResources,
                required: true,
                options: {
                  min: 0
                }
              }
            ]
          }, {
            name: 'restrictions.enableMaxStorage',
            type: 'boolean',
            label: trans('restrict_max_storage'),
            calculated: restrictStorage,
            displayed: props.isAdmin,
            onChange: activated => {
              if (!activated) {
                // reset max users field
                props.updateProp('restrictions.maxStorage', null)
              }
            },
            linked: [
              {
                name: 'restrictions.maxStorage',
                type: 'storage',
                label: trans('max_storage_size'),
                displayed: restrictStorage,
                required: true,
                options: {
                  min: 0
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
  updateProp: T.func.isRequired
}

const WorkspaceForm = connect(
  (state, ownProps) => ({
    isAdmin: securitySelectors.isAdmin(state),
    hasBreadcrumb: configSelectors.param(state, 'display.breadcrumb'),
    new: formSelect.isNew(formSelect.form(state, ownProps.name)),
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
