import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {trans} from '#/main/core/translation'
import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

import {Team as TeamType} from '#/plugin/team/tools/team/prop-types'
import {actions, selectors} from '#/plugin/team/tools/team/store'

const TeamFormComponent = props =>
  <section className="tool-section">
    <h2>{props.isNew ? trans('team_creation', {}, 'team') : trans('team_edition', {}, 'team')}</h2>
    <FormData
      level={3}
      name="teams.current"
      buttons={true}
      target={(team, isNew) => isNew ?
        ['apiv2_team_create'] :
        ['apiv2_team_update', {id: team.id}]
      }
      // save={{
      //   type: CALLBACK_BUTTON,
      //   target: `/teams/${props.team.id}`,
      //   callback: () => props.history.push(`/teams/${props.team.id}`)
      // }}
      cancel={{
        type: LINK_BUTTON,
        target: '/',
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
              label: trans('description')
            }, {
              name: 'defaultResource',
              type: 'resource',
              label: trans('default_resource', {}, 'team'),
              displayed: props.isNew
            }, {
              name: 'maxUsers',
              type: 'number',
              label: trans('max_users', {}, 'team')
            }, {
              name: 'publicDirectory',
              type: 'boolean',
              label: trans('team_directory_public_access', {}, 'team'),
              required: true
            }, {
              name: 'deletableDirectory',
              type: 'boolean',
              label: trans('delete_team_directory', {}, 'team'),
              required: true
            }, {
              name: 'selfRegistration',
              type: 'boolean',
              label: trans('team_self_registration', {}, 'team'),
              required: true
            }, {
              name: 'selfUnregistration',
              type: 'boolean',
              label: trans('team_self_unregistration', {}, 'team'),
              required: true
            }, {
              name: 'creatableResources',
              type: 'choice',
              label: trans('user_creatable_resources', {}, 'team'),
              displayed: props.isNew,
              options: {
                multiple: true,
                condensed: true,
                choices: props.resourceTypes.reduce((acc, type) => {
                  acc[type] = trans(type, {}, 'resource')

                  return acc
                }, {})
              }
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
                callback: () => props.pickUsers(props.team.id, props.workspaceId)
              }
            ]}
          >
            <ListData
              name="teams.current.users"
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
                callback: () => props.pickUsers(props.team.id, props.workspaceId, true)
              }
            ]}
          >
            <ListData
              name="teams.current.managers"
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

TeamFormComponent.propTypes = {
  team: T.shape(TeamType.propTypes).isRequired,
  workspaceId: T.string.isRequired,
  isNew: T.bool.isRequired,
  resourceTypes: T.arrayOf(T.string).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  pickUsers: T.func.isRequired
}

const TeamForm = connect(
  (state) => ({
    team: formSelectors.data(formSelectors.form(state, 'teams.current')),
    workspaceId: workspaceSelect.workspace(state).uuid,
    isNew: formSelectors.isNew(formSelectors.form(state, 'teams.current')),
    resourceTypes: selectors.resourceTypes(state)
  }),
  (dispatch) => ({
    pickUsers(teamId, workspaceId, pickManagers = false) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: pickManagers ? trans('add_managers', {}, 'team') : trans('add_members', {}, 'team'),
        confirmText: trans('add'),
        name: 'teams.current.usersPicker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_workspace_list_users', {id: workspaceId}],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.registerUsers(teamId, selected, pickManagers ? 'manager' : 'user'))
      }))
    }
  })
)(TeamFormComponent)

export {
  TeamForm
}