import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentSection, ContentSections} from '#/main/app/content/components/sections'
import {Alert} from '#/main/app/alert/components/alert'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'

import {Team as TeamTypes} from '#/main/community/team/prop-types'
import {TeamPage} from '#/main/community/team/components/page'
import {selectors} from '#/main/community/tools/community/team/store/selectors'

const TeamShow = (props) => {
  const full = !!get(props.team, 'restrictions.users') && props.team.users >= get(props.team, 'restrictions.users')

  return (
    <TeamPage
      path={props.path}
      team={props.team}
      reload={props.reload}
    >
      <DetailsData
        name={selectors.FORM_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'meta.description',
                type: 'string',
                label: trans('description'),
                hideLabel: true,
                displayed: (team) => get(team, 'meta.description'),
                options: {
                  long: true
                }
              }, {
                name: 'directory',
                label: trans('directory', {}, 'resource'),
                type: 'resource',
                displayed: (team) => !isEmpty(team.directory)
              }, {
                name: 'managers',
                label: trans('managers', {}, 'community'),
                type: 'users',
                displayed: (team) => !isEmpty(team.managers)
              }
            ]
          }
        ]}
      />

      {!full && get(props.team, 'registration.selfRegistration') &&
        <Alert type="info">
          {trans('team_self_registration_enabled', {}, 'community')}
        </Alert>
      }

      {full &&
        <Alert type="warning">
          {trans('team_full', {}, 'community')}
        </Alert>
      }

      {!props.hasTeam && !full && get(props.team, 'registration.selfRegistration') &&
        <Button
          className="btn btn-emphasis btn-block component-container"
          type={CALLBACK_BUTTON}
          label={trans('self_register', {}, 'actions')}
          callback={() => props.selfRegister(props.team)}
          primary={true}
        />
      }

      {props.hasTeam && get(props.team, 'registration.selfUnregistration') &&
        <Button
          className="btn btn-emphasis btn-block component-container"
          type={CALLBACK_BUTTON}
          label={trans('self_unregister', {}, 'actions')}
          callback={() => props.selfUnregister(props.team)}
          dangerous={true}
        />
      }

      <ContentSections level={3} defaultOpened="team-users">
        <ContentSection
          id="team-users"
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('users', {}, 'community')}
          disabled={!props.team.id}
          actions={[
            {
              name: 'add-users',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_users'),
              displayed: hasPermission('edit', props.team),
              disabled: full,
              modal: [MODAL_USERS, {
                url: ['apiv2_workspace_list_users', {id: props.contextData.id}],
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => props.addUsers(props.team.id, selected)
                })
              }]
            }
          ]}
        >
          <UserList
            path={props.path}
            name={`${selectors.FORM_NAME}.users`}
            url={['apiv2_team_list_users', {id: get(props.team, 'id'), role: 'user'}]}
            autoload={!!props.team.id}
            delete={{
              url: ['apiv2_team_unregister', {id: get(props.team, 'id'), role: 'user'}],
              label: trans('unregister', {}, 'actions'),
              displayed: () => hasPermission('edit', props.team)
            }}
            actions={undefined}
          />
        </ContentSection>

        <ContentSection
          id="team-managers"
          className="embedded-list-section"
          icon="fa fa-fw fa-user-tie"
          title={trans('managers', {}, 'community')}
          disabled={!props.team.id}
          actions={[
            {
              name: 'add-users',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_users'),
              displayed: hasPermission('edit', props.team),
              modal: [MODAL_USERS, {
                url: ['apiv2_workspace_list_users', {id: props.contextData.id}],
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => props.addManagers(props.team.id, selected)
                })
              }]
            }
          ]}
        >
          <UserList
            path={props.path}
            name={`${selectors.FORM_NAME}.managers`}
            url={['apiv2_team_list_users', {id: get(props.team, 'id'), role: 'manager'}]}
            autoload={!!props.team.id}
            delete={{
              url: ['apiv2_team_unregister', {id: get(props.team, 'id'), role: 'manager'}],
              label: trans('unregister', {}, 'actions'),
              displayed: () => hasPermission('edit', props.team)
            }}
            actions={undefined}
          />
        </ContentSection>
      </ContentSections>
    </TeamPage>
  )
}

TeamShow.propTypes = {
  path: T.string.isRequired,
  contextData: T.object.isRequired,
  team: T.shape(
    TeamTypes.propTypes
  ),
  hasTeam: T.bool.isRequired,
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addManagers: T.func.isRequired,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired
}

export {
  TeamShow
}
