import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {UserList} from '#/main/core/administration/community/user/components/user-list'

import {Team as TeamType} from '#/plugin/team/tools/team/prop-types'
import {selectors} from '#/plugin/team/tools/team/store'

const TeamForm = props =>
  <section className="tool-section">
    <h2>{props.isNew ? trans('team_creation', {}, 'team') : trans('team_edition', {}, 'team')}</h2>
    <FormData
      level={3}
      name={selectors.STORE_NAME + '.teams.current'}
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
              name: 'directory',
              type: 'resource',
              label: trans('public_directory', {}, 'team'),
              displayed: (team) => !!team.directory
            }, {
              name: 'createPublicDirectory',
              type: 'boolean',
              label: trans('team_create_public_directory', {}, 'team'),
              displayed: (team) => !team.directory,
              linked: [
                {
                  name: 'publicDirectory',
                  type: 'boolean',
                  label: trans('team_directory_public_access', {}, 'team'),
                  displayed: !!props.team.createPublicDirectory
                }, {
                  name: 'deletableDirectory',
                  type: 'boolean',
                  label: trans('delete_team_directory', {}, 'team'),
                  displayed: !!props.team.createPublicDirectory
                }, {
                  name: 'defaultResource',
                  type: 'resource',
                  label: trans('default_resource', {}, 'team'),
                  displayed: !!props.team.createPublicDirectory
                }, {
                  name: 'creatableResources',
                  type: 'choice',
                  label: trans('user_creatable_resources', {}, 'team'),
                  displayed: !!props.team.createPublicDirectory,
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
    >
      <FormSections level={3}>
        {props.team.role &&
          <FormSection
            className="embedded-list-section"
            icon="fa fa-fw fa-users"
            title={trans('team_members', {}, 'team')}
            disabled={props.isNew}
            actions={[
              {
                type: 'callback',
                icon: 'fa fa-fw fa-plus',
                label: trans('add_members', {}, 'team'),
                callback: () => props.pickUsers(props.team.id, props.workspace.id)
              }
            ]}
          >
            <ListData
              name={selectors.STORE_NAME + '.teams.current.users'}
              fetch={{
                url: ['apiv2_role_list_users', {id: props.team.role.id}],
                autoload: !props.isNew
              }}
              delete={{
                url: ['apiv2_team_unregister', {team: props.team.id, role: 'user'}]
              }}
              definition={UserList.definition}
              card={UserList.card}
            />
          </FormSection>
        }
        {props.team.teamManagerRole &&
          <FormSection
            className="embedded-list-section"
            icon="fa fa-fw fa-user-graduate"
            title={trans('team_managers', {}, 'team')}
            disabled={props.isNew}
            actions={[
              {
                type: 'callback',
                icon: 'fa fa-fw fa-plus',
                label: trans('add_managers', {}, 'team'),
                callback: () => props.pickUsers(props.team.id, props.workspace.id, true)
              }
            ]}
          >
            <ListData
              name={selectors.STORE_NAME + '.teams.current.managers'}
              fetch={{
                url: ['apiv2_role_list_users', {id: props.team.teamManagerRole.id}],
                autoload: !props.isNew
              }}
              delete={{
                url: ['apiv2_team_unregister', {team: props.team.id, role: 'manager'}]
              }}
              definition={UserList.definition}
              card={UserList.card}
            />
          </FormSection>
        }
      </FormSections>
    </FormData>
  </section>

TeamForm.propTypes = {
  path: T.string.isRequired,
  team: T.shape(TeamType.propTypes).isRequired,
  workspace: T.shape({
    id: T.string.isRequired
  }).isRequired,
  isNew: T.bool.isRequired,
  resourceTypes: T.arrayOf(T.object).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  update: T.func.isRequired,
  pickUsers: T.func.isRequired
}

export {
  TeamForm
}
