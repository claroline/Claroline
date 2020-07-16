import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {UserList} from '#/main/core/administration/community/user/components/user-list'

import {selectors} from '#/plugin/team/tools/team/store'
import {Team as TeamType} from '#/plugin/team/tools/team/prop-types'

const getUserActions = (team, myTeams, totalUsers, allowedTeams, register, unregister) => {
  const actions = []

  if (canRegister(team, myTeams, totalUsers, allowedTeams)) {
    actions.push({
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-sign-in',
      label: trans('self_register', {}, 'team'),
      callback: () => register(team.id)
    })
  }
  if (canUnregister(team, myTeams)) {
    actions.push({
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-sign-out',
      label: trans('self_unregister', {}, 'team'),
      callback: () => unregister(team.id)
    })
  }

  return actions
}

const canRegister = (team, myTeams, totalUsers, allowedTeams) => {
  return team.selfRegistration &&
    -1 === myTeams.indexOf(team.id) &&
    (!team.maxUsers || totalUsers < team.maxUsers) &&
    (!allowedTeams || allowedTeams > myTeams.length)
}

const canUnregister = (team, myTeams) => {
  return team.selfUnregistration && 0 <= myTeams.indexOf(team.id)
}

const Team = props =>
  <div>
    {props.canEdit &&
      <LinkButton
        className="btn-link page-actions-btn pull-right"
        disabled={!props.canEdit}
        target={`${props.path}/team/form/${props.team.id}`}
      >
        <span className="fa fa-fw fa-pencil" />
      </LinkButton>
    }
    <DetailsData
      name={selectors.STORE_NAME + '.teams.current'}
      title={props.team.name}
      sections={[
        {
          id: 'general',
          icon: 'fa fa-fw fa-cogs',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('description'),
              options: {
                workspace: props.workspace
              }
            }
          ]
        }
      ]}
    />
    <FormSections level={3}>
      {props.team.role &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-users"
          title={trans('team_members', {}, 'team')}
          actions={getUserActions(
            props.team,
            props.myTeams,
            props.teamTotalUsers,
            props.allowedTeams,
            props.selfRegister,
            props.selfUnregister
          )}
        >
          <ListData
            name={selectors.STORE_NAME + '.teams.current.users'}
            fetch={{
              url: ['apiv2_role_list_users', {id: props.team.role.id}],
              autoload: true
            }}
            definition={UserList.definition}
            card={UserList.card}
          />
        </FormSection>
      }
      {props.team.teamManagerRole &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-atom"
          title={trans('team_managers', {}, 'team')}
        >
          <ListData
            name={selectors.STORE_NAME + '.teams.current.managers'}
            fetch={{
              url: ['apiv2_role_list_users', {id: props.team.teamManagerRole.id}],
              autoload: true
            }}
            definition={UserList.definition}
            card={UserList.card}
          />
        </FormSection>
      }
    </FormSections>
  </div>

Team.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  team: T.shape(TeamType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  allowedTeams: T.number,
  myTeams: T.arrayOf(T.string),
  teamTotalUsers: T.number,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired
}

export {
  Team
}