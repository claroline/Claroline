import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'

import {FormContainer} from '#/main/core/data/form/containers/form'
import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'

// easy selection for restrictions
const restrictByDates   = (workspace) => workspace.restrictions.enableDates        || (workspace.restrictions.dates && 0 !== workspace.restrictions.dates.length)
const restrictUsers     = (workspace) => workspace.restrictions.enableMaxUsers     || 0 === workspace.restrictions.maxUsers || !!workspace.restrictions.maxUsers
const restrictResources = (workspace) => workspace.restrictions.enableMaxResources || 0 === workspace.restrictions.maxResources || !!workspace.restrictions.maxResources
const restrictStorage   = (workspace) => workspace.restrictions.enableMaxStorage   || !!workspace.restrictions.maxStorage

// TODO : finish implementation (open resource / open tool)
// TODO : add tools

const WorkspaceFormComponent = (props) =>
  <FormContainer
    level={props.level}
    name={props.name}
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
                type: 'string',
                label: trans('tool'),
                required: true,
                displayed: (workspace) => workspace.opening && 'tool' === workspace.opening.type
              }, {
                name: 'opening.target',
                type: 'string',
                label: trans('resource'),
                required: true,
                displayed: (workspace) => workspace.opening && 'resource' === workspace.opening.type
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
          }, {
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
  </FormContainer>

WorkspaceFormComponent.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  children: T.any,

  // from redux
  new: T.bool.isRequired,
  updateProp: T.func.isRequired
}

WorkspaceFormComponent.defaultProps = {
  level: 3
}

const WorkspaceForm = connect(
  (state, ownProps) => ({
    new: formSelect.isNew(formSelect.form(state, ownProps.name))
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
