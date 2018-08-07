import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {select as listSelectors} from '#/main/app/content/list/store/selectors'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {trans} from '#/main/core/translation'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

import {selectors, actions} from '#/plugin/team/tools/team/store'
import {Team as TeamType} from '#/plugin/team/tools/team/prop-types'

const getUserActions = (team, myTeams, totalUsers, allowedTeams, register, unregister) => {
  const actions = []

  if (canRegister(team, myTeams, totalUsers, allowedTeams)) {
    actions.push({
      type: 'callback',
      icon: 'fa fa-fw fa-sign-in',
      label: trans('self_register', {}, 'team'),
      callback: () => register(team.id)
    })
  }
  if (canUnregister(team, myTeams)) {
    actions.push({
      type: 'callback',
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

const TeamComponent = props =>
  <div>
    {props.canEdit &&
      <LinkButton
        className="btn-link page-actions-btn pull-right"
        disabled={!props.canEdit}
        target={`/team/form/${props.team.id}`}
      >
        <span className="fa fa-fw fa-pencil" />
      </LinkButton>
    }
    <DetailsData
      name="teams.current"
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
              label: trans('description')
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
            name="teams.current.users"
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
          icon="fa fa-fw fa-graduation-cap"
          title={trans('team_managers', {}, 'team')}
        >
          <ListData
            name="teams.current.managers"
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

TeamComponent.propTypes = {
  team: T.shape(TeamType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  allowedTeams: T.number,
  myTeams: T.arrayOf(T.string),
  teamTotalUsers: T.number,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired
}

const Team = connect(
  (state) => ({
    team: formSelectors.data(formSelectors.form(state, 'teams.current')),
    canEdit: selectors.canEdit(state),
    allowedTeams: selectors.allowedTeams(state),
    myTeams: selectors.myTeams(state),
    teamTotalUsers: listSelectors.totalResults(listSelectors.list(state, 'teams.current.users'))
  }),
  (dispatch) => ({
    selfRegister(teamId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('register_to_team', {}, 'team'),
        question: trans('register_to_team_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.selfRegister(teamId))
      }))
    },
    selfUnregister(teamId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('unregister_from_team', {}, 'team'),
        question: trans('unregister_from_team_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.selfUnregister(teamId))
      }))
    }
  })
)(TeamComponent)


export {
  Team
}