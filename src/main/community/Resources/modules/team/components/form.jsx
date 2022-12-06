import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as formActions} from '#/main/app/content/form/store'

const TeamFormComponent = props =>
  <FormData
    level={3}
    name={props.name}
    buttons={true}
    target={(team, isNew) => isNew ?
      ['apiv2_team_create'] :
      ['apiv2_team_update', {id: team.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true,
            disabled: (team) => get(team, 'meta.readOnly')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-circle-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            type: 'string',
            label: trans('description'),
            options: {
              long: true
            }
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.selfRegistration',
            type: 'boolean',
            label: trans('activate_self_registration'),
            help: trans('team_self_registration_help', {}, 'community')
          }, {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: trans('activate_self_unregistration'),
            help: trans('team_self_unregistration_help', {}, 'community')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-folder',
        title: trans('directory', {}, 'resource'),
        fields: [
          {
            name: 'directory',
            type: 'resource',
            label: trans('directory', {}, 'resource'),
            displayed: (team) => !!team.directory
          }, {
            name: 'createPublicDirectory',
            type: 'boolean',
            label: trans('team_create_public_directory', {}, 'community'),
            displayed: (team) => !team.directory,
            linked: [
              {
                name: 'publicDirectory',
                type: 'boolean',
                label: trans('team_directory_public_access', {}, 'community'),
                displayed: (team) => !!team.createPublicDirectory
              }, {
                name: 'deletableDirectory',
                type: 'boolean',
                label: trans('delete_team_directory', {}, 'community'),
                displayed: (team) => !!team.createPublicDirectory
              }, {
                name: 'defaultResource',
                type: 'resource',
                label: trans('team_default_resource', {}, 'community'),
                displayed: (team) => !!team.createPublicDirectory
              }, {
                name: 'creatableResources',
                type: 'choice',
                label: trans('team_creatable_resources', {}, 'community'),
                displayed: (team) => !!team.createPublicDirectory,
                options: {
                  multiple: true,
                  condensed: false,
                  inline: false,
                  choices: props.resourceTypes.reduce((acc, type) => {
                    acc[type.name] = trans(type.name, {}, 'resource')

                    return acc
                  }, {})
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions._restrictUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (team) => get(team, 'restrictions.users') || get(team, 'restrictions._restrictUsers'),
            onChange: (value) => {
              if (!value) {
                props.updateProp('restrictions.users', null)
              }
            },
            linked: [
              {
                name: 'restrictions.users',
                type: 'number',
                label: trans('users_count'),
                required: true,
                displayed: (team) => get(team, 'restrictions.users') || get(team, 'restrictions._restrictUsers'),
                options: {
                  min: 0
                }
              }
            ]
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

TeamFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  children: T.any,

  // from store
  resourceTypes: T.array,
  updateProp: T.func.isRequired
}

const TeamForm = connect(
  (state) => ({
    resourceTypes: configSelectors.param(state, 'resources.types')
  }),
  (dispatch, ownProps) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(TeamFormComponent)

export {
  TeamForm
}
