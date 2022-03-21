import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {UserList} from '#/main/core/user/components/list'

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
  return team.registration && team.registration.selfRegistration &&
    -1 === myTeams.indexOf(team.id) &&
    (!team.maxUsers || totalUsers < team.maxUsers) &&
    (!allowedTeams || allowedTeams > myTeams.length)
}

const canUnregister = (team, myTeams) => {
  return team.selfUnregistration && 0 <= myTeams.indexOf(team.id)
}

const Team = props => {
  if (isEmpty(props.team)) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons l'équipe..."
      />
    )
  }

  return (
    <Fragment>
      <ContentTitle
        title={props.team.name}
        backAction={{
          type: LINK_BUTTON,
          target: `${props.path}/teams`,
          exact: true
        }}
        actions={[
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            displayed: props.canEdit,
            target: `${props.path}/team/form/${props.team.id}`
          }
        ]}
      />

      <DetailsData
        name={selectors.STORE_NAME + '.teams.current'}
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
              }, {
                name: 'directory',
                type: 'resource',
                label: trans('public_directory', {}, 'team'),
                displayed: (team) => !!team.directory
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
            <UserList
              name={selectors.STORE_NAME + '.teams.current.users'}
              url={['apiv2_role_list_users', {id: props.team.role.id}]}
            />
          </FormSection>
        }

        {props.team.teamManagerRole &&
          <FormSection
            className="embedded-list-section"
            icon="fa fa-fw fa-atom"
            title={trans('team_managers', {}, 'team')}
          >
            <UserList
              name={selectors.STORE_NAME + '.teams.current.managers'}
              url={['apiv2_role_list_users', {id: props.team.teamManagerRole.id}]}
            />
          </FormSection>
        }
      </FormSections>
    </Fragment>
  )
}

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