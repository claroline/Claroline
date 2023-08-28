import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/community/team/routing'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const TeamFormComponent = props => {
  const history = useHistory()

  return (
    <FormData
      level={3}
      name={props.name}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.save(props.team, props.isNew, props.name).then(team => {
          history.push(route(team))
        })
      }}
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
  )
}

TeamFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  isNew: T.bool.isRequired,
  team: T.object.isRequired,
  children: T.any,

  // from store
  resourceTypes: T.array,
  save: T.func.isRequired,
  updateProp: T.func.isRequired
}

const TeamForm = connect(
  (state, ownProps) => ({
    resourceTypes: configSelectors.param(state, 'resources.types'),
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    team: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    save(team, isNew, name) {
      return dispatch( formActions.saveForm(name, isNew ?
        ['apiv2_team_create'] :
        ['apiv2_team_update', {id: team.id}])
      )
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(TeamFormComponent)

export {
  TeamForm
}
