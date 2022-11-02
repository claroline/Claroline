import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {selectors as configSelectors} from '#/main/app/config/store'
import {actions, selectors as workspaceSelectors} from '#/main/core/workspace/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {route} from '#/main/core/workspace/routing'

// easy selection for restrictions
const restrictByDates = (workspace) => get(workspace, 'restrictions.enableDates') || !isEmpty(get(workspace, 'restrictions.dates'))
const restrictByCode  = (workspace) => get(workspace, 'restrictions.enableCode') || !!get(workspace, 'restrictions.code')
const restrictByIps   = (workspace) => get(workspace, 'restrictions.enableIps') || !isEmpty(get(workspace, 'restrictions.allowedIps'))

// easy selection for evaluation
const enableSuccessCondition = (workspace) => get(workspace, 'evaluation._enableSuccess')
  || get(workspace, 'evaluation.successCondition.score')
  || undefined !== get(workspace, 'evaluation.successCondition.minSuccess')
  || undefined !== get(workspace, 'evaluation.successCondition.maxFailed')
const enableSuccessScore = (workspace) => get(workspace, 'evaluation._enableSuccessScore') || get(workspace, 'evaluation.successCondition.score')
const enableSuccessMinSuccess = (workspace) => get(workspace, 'evaluation._enableSuccessCount') || null !== get(workspace, 'evaluation.successCondition.minSuccess', null)
const enableSuccessMaxFailed = (workspace) => get(workspace, 'evaluation._enableFailureCount') || null !== get(workspace, 'evaluation.successCondition.maxFailed', null)

const WorkspaceFormComponent = (props) =>
  <FormData
    meta={true}
    {...props}
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
            name: 'model',
            type: 'workspace',
            label: trans('create_from_model'),
            options: {
              picker: {
                model: true,
                title: trans('workspace_models', {}, 'workspace')
              }
            },
            displayed: props.new,
            mode: 'standard',
            onChange: (model) => props.loadModel(model)
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
            name: 'organizations',
            type: 'organizations',
            label: trans('organizations'),
            mode: 'standard'
          }, {
            name: 'contactEmail',
            label: trans('contact'),
            type: 'email',
            mode: 'standard'
          }, {
            name: 'meta.model',
            label: trans('workspace_model', {}, 'workspace'),
            type: 'boolean',
            disabled: !props.new,
            mode: 'expert'
          }, {
            name: 'meta.personal',
            label: trans('workspace_personal', {}, 'workspace'),
            type: 'boolean',
            disabled: true,
            displayed: !props.new,
            mode: 'expert'
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
        icon: 'fa fa-fw fa-award',
        title: trans('evaluation'),
        fields: [
          {
            name: 'evaluation._enableSuccess',
            type: 'boolean',
            label: trans('enable_success_condition', {}, 'workspace'),
            help: trans('enable_success_condition_help', {}, 'workspace'),
            calculated: enableSuccessCondition,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('evaluation.successCondition', null)
                props.updateProp('evaluation._enableSuccessScore', false)
                props.updateProp('evaluation._enableSuccessCount', false)
                props.updateProp('evaluation._enableFailureCount', false)
              }
            },
            linked: [
              {
                name: 'evaluation._enableSuccessScore',
                label: trans('Obtenir un score minimal', {}, 'workspace'),
                type: 'boolean',
                displayed: enableSuccessCondition,
                calculated: enableSuccessScore,
                onChange: (enabled) => {
                  if (!enabled) {
                    props.updateProp('evaluation.successCondition.score', null)
                  }
                },
                linked: [
                  {
                    name: 'evaluation.successCondition.score',
                    label: trans('score_to_pass'),
                    type: 'number',
                    required: true,
                    displayed: enableSuccessScore,
                    options: {
                      min: 0,
                      max: 100,
                      unit: '%'
                    }
                  }
                ]
              }, {
                name: 'evaluation._enableSuccessCount',
                type: 'boolean',
                label: trans('enable_success_condition_success', {}, 'workspace'),
                displayed: enableSuccessCondition,
                calculated: enableSuccessMinSuccess,
                onChange: (enabled) => {
                  if (!enabled) {
                    props.updateProp('evaluation.successCondition.minSuccess', null)
                  }
                },
                linked: [
                  {
                    name: 'evaluation.successCondition.minSuccess',
                    label: trans('count_resources', {}, 'resource'),
                    type: 'number',
                    required: true,
                    displayed: enableSuccessMinSuccess,
                    options: {
                      min: 0
                    }
                  }
                ]
              }, {
                name: 'evaluation._enableFailureCount',
                type: 'boolean',
                label: trans('enable_success_condition_failed', {}, 'workspace'),
                displayed: enableSuccessCondition,
                calculated: enableSuccessMaxFailed,
                onChange: (enabled) => {
                  if (!enabled) {
                    props.updateProp('evaluation.successCondition.maxFailed', null)
                  }
                },
                linked: [
                  {
                    name: 'evaluation.successCondition.maxFailed',
                    label: trans('count_resources', {}, 'resource'),
                    type: 'number',
                    required: true,
                    displayed: enableSuccessMaxFailed,
                    options: {
                      min: 0
                    }
                  }
                ]
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
  hasBreadcrumb: T.bool.isRequired,
  new: T.bool.isRequired,
  id: T.string,
  loadModel: T.func.isRequired,
  updateProp: T.func.isRequired
}

const WorkspaceForm = connect(
  (state, ownProps) => ({
    hasBreadcrumb: configSelectors.param(state, 'display.breadcrumb'),
    new: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    id: formSelectors.data(formSelectors.form(state, ownProps.name)).id,
    // todo : fix tool/resource selection for opening. Those values are only available if the workspace is opened
    tools: workspaceSelectors.tools(state),
    root: workspaceSelectors.root(state)
  }),
  (dispatch, ownProps) =>({
    loadModel(model) {
      dispatch(actions.fetchModel(model.id)).then((workspaceModel) => {
        const newWorkspace = merge({}, workspaceModel, {
          // reset some values
          model: model,
          meta: {
            model: false
          },
          roles: [] // should disappear from response once transfer is rewritten
        })

        delete newWorkspace.id
        delete newWorkspace.autoId
        if (newWorkspace.registration && newWorkspace.registration.defaultRole) {
          // otherwise new workspace will directly use the model role
          delete newWorkspace.registration.defaultRole
        }

        dispatch(formActions.update(ownProps.name, newWorkspace))
      })
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(WorkspaceFormComponent)

export {
  WorkspaceForm
}
