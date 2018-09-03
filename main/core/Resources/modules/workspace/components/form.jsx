import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'

import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

// easy selection for restrictions
const restrictByDates   = (workspace) => workspace.restrictions.enableDates        || (workspace.restrictions.dates && 0 !== workspace.restrictions.dates.length)
const restrictUsers     = (workspace) => workspace.restrictions.enableMaxUsers     || 0 === workspace.restrictions.maxUsers || !!workspace.restrictions.maxUsers
const restrictResources = (workspace) => workspace.restrictions.enableMaxResources || 0 === workspace.restrictions.maxResources || !!workspace.restrictions.maxResources
const restrictStorage   = (workspace) => workspace.restrictions.enableMaxStorage   || !!workspace.restrictions.maxStorage

// TODO : add tools

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
            }
          }, {
            name: 'meta.model',
            label: trans('model'),
            type: 'boolean',
            disabled: !props.new
          }, {
            name: 'meta.personal',
            label: trans('personal'),
            type: 'boolean',
            disabled: true
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
            linked: [{
              name: 'meta.lang',
              label: trans('lang'),
              type: 'locale',
              displayed: (workspace) => workspace.meta.forceLang
            }]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('opening_parameters'),
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
            onChange: () => {
              props.updateProp('opening.target', null)
            },
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
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          },
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          },
          {
            name: 'display.color',
            type: 'color',
            label: trans('color')
          }, {
            name: 'display.showBreadcrumbs',
            type: 'boolean',
            label: trans('showBreadcrumbs')
          }, {
            name: 'display.showTools',
            type: 'boolean',
            label: trans('showTools')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.url',
            type: 'url',
            label: trans('registration_url'),
            calculated: (workspace) => url(['claro_workspace_subscription_url_generate', {slug: workspace.meta ? workspace.meta.slug : ''}, true]),
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
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
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
            name: 'restrictions.enableMaxUsers',
            type: 'boolean',
            label: trans('restrict_max_users'),
            calculated: restrictUsers,
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
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  children: T.any,
  tools: T.array,

  // from redux
  new: T.bool.isRequired,
  updateProp: T.func.isRequired
}

WorkspaceFormComponent.defaultProps = {
  modal: false
}

const WorkspaceForm = connect(
  (state, ownProps) => ({
    workspace: formSelect.data(formSelect.form(state, ownProps.name)),
    new: formSelect.isNew(formSelect.form(state, ownProps.name)),
    tools: workspaceSelect.tools(state)
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
