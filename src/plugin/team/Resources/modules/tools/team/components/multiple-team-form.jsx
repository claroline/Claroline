import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/team/tools/team/store'

const MultipleTeamForm = props =>
  <section className="tool-section">
    <h2>{trans('multiple_teams_creation', {}, 'team')}</h2>
    <FormData
      level={3}
      name={selectors.STORE_NAME + '.teams.multiple'}
      buttons={true}
      target={['apiv2_team_multiple_create', {workspace: props.workspace.id}]}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      sections={[
        {
          id: 'general',
          icon: 'fa fa-fw fa-cogs',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              options: {
                workspace: props.workspace
              }
            }, {
              name: 'nbTeams',
              type: 'number',
              label: trans('nb_teams', {}, 'team'),
              required: true,
              options: {
                min: 1
              }
            }, {
              name: 'defaultResource',
              type: 'resource',
              label: trans('default_resource', {}, 'team')
            }, {
              name: 'publicDirectory',
              type: 'boolean',
              label: trans('team_directory_public_access', {}, 'team')
            }, {
              name: 'deletableDirectory',
              type: 'boolean',
              label: trans('delete_team_directory', {}, 'team')
            }, {
              name: 'createPublicDirectory',
              type: 'boolean',
              label: trans('team_create_public_directory', {}, 'team'),
              linked: [
                {
                  name: 'publicDirectory',
                  type: 'boolean',
                  label: trans('team_directory_public_access', {}, 'team'),
                  displayed: (team) => !!team.createPublicDirectory
                }, {
                  name: 'deletableDirectory',
                  type: 'boolean',
                  label: trans('delete_team_directory', {}, 'team'),
                  displayed: (team) => !!team.createPublicDirectory
                }, {
                  name: 'defaultResource',
                  type: 'resource',
                  label: trans('default_resource', {}, 'team'),
                  displayed: (team) => !!team.createPublicDirectory
                }, {
                  name: 'creatableResources',
                  type: 'choice',
                  label: trans('user_creatable_resources', {}, 'team'),
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
          icon: 'fa fa-fw fa-sign-in',
          title: trans('registration'),
          fields: [
            {
              name: 'registration.selfRegistration',
              type: 'boolean',
              label: trans('activate_self_registration'),
              help: trans('self_registration_help', {}, 'team')
            }, {
              name: 'registration.selfUnregistration',
              type: 'boolean',
              label: trans('activate_self_unregistration'),
              help: trans('self_unregistration_help', {}, 'team')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            {
              name: '_restrictUsers',
              type: 'boolean',
              label: trans('restrict_users_count'),
              calculated: (team) => get(team, 'maxUsers') || get(team, '_restrictUsers'),
              onChange: (value) => {
                if (!value) {
                  props.update('maxUsers', null)
                }
              },
              linked: [
                {
                  name: 'maxUsers',
                  type: 'number',
                  label: trans('users_count'),
                  required: true,
                  displayed: (team) => get(team, 'maxUsers') || get(team, '_restrictUsers'),
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  </section>

MultipleTeamForm.propTypes = {
  path: T.string.isRequired,
  form: T.shape({
    name: T.string,
    description: T.string,
    nbTeams: T.number,
    registrations: T.shape({
      selfRegistration: T.bool.isRequired,
      selfUnregistration: T.bool.isRequired
    }),
    publicDirectory: T.bool.isRequired,
    deletableDirectory: T.bool.isRequired,
    maxUsers: T.number,
    defaultResource: T.object,
    creatableResources: T.arrayOf(T.string)
  }).isRequired,
  workspace: T.shape({
    id: T.string.isRequired
  }).isRequired,
  resourceTypes: T.arrayOf(T.object).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  update: T.func.isRequired
}

export {
  MultipleTeamForm
}