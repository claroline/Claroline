import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {
  PageContainer,
  PageHeader,
  PageActions,
  PageAction,
  MoreAction
} from '#/main/core/layout/page'
import {RoutedPageContent} from '#/main/core/layout/router/components/page'

import {selectors, actions} from '#/plugin/team/tools/team/store'
import {TeamParams as TeamParamsType} from '#/plugin/team/tools/team/prop-types'
import {Editor} from '#/plugin/team/tools/team/components/editor'
import {Teams} from '#/plugin/team/tools/team/components/teams'
import {Team} from '#/plugin/team/tools/team/components/team'
import {TeamForm} from '#/plugin/team/tools/team/components/team-form'
import {MultipleTeamForm} from '#/plugin/team/tools/team/components/multiple-team-form'

const TeamToolComponent = props =>
  <PageContainer>
    <PageHeader title={trans('team', {}, 'team')}>
      {props.canEdit ?
        <PageActions>
          <PageAction
            id="team-add"
            type="link"
            icon="fa fa-fw fa-plus"
            primary={true}
            label={trans('create_a_team', {}, 'team')}
            target="/team/form"
            exact={true}
          />
          <PageAction
            id="team-params"
            type="link"
            icon="fa fa-fw fa-cog"
            label={trans('configure')}
            target="/edit"
          />
          <MoreAction
            actions={[
              {
                type: 'link',
                icon: 'fa fa-fw fa-home',
                label: trans('home'),
                target: '/teams',
                exact: true
              }, {
                type: 'link',
                icon: 'fa fa-fw fa-user-plus',
                label: trans('create_teams', {}, 'team'),
                target: '/teams/multiple/form'
              }
            ]}
          />
        </PageActions> :
        <PageActions>
          <PageAction
            id="team-home"
            type="link"
            icon="fa fa-fw fa-home"
            primary={true}
            label={trans('home')}
            target="/teams"
            exact={true}
          />
        </PageActions>
      }
    </PageHeader>
    <RoutedPageContent
      key="team-tool-content"
      headerSpacer={true}
      redirect={[
        {from: '/', exact: true, to: '/teams'}
      ]}
      routes={[
        {
          path: '/edit',
          component: Editor,
          disabled: !props.canEdit,
          onLeave: () => props.resetForm(),
          onEnter: () => props.resetForm(props.teamParams)
        }, {
          path: '/teams',
          component: Teams,
          exact: true
        }, {
          path: '/teams/:id',
          component: Team,
          onEnter: (params) => props.openCurrentTeam(params.id, props.teamParams, props.workspaceId),
          onLeave: () => props.resetCurrentTeam(),
          exact: true
        }, {
          path: '/team/form/:id?',
          component: TeamForm,
          disabled: !props.canEdit,
          onEnter: (params) => props.openCurrentTeam(params.id, props.teamParams, props.workspaceId, props.resourceTypes),
          onLeave: () => props.resetCurrentTeam()
        }, {
          path: '/teams/multiple/form',
          component: MultipleTeamForm,
          disabled: !props.canEdit,
          onEnter: () => props.openMultipleTeamsForm(props.teamParams, props.resourceTypes),
          onLeave: () => props.resetMultipleTeamsForm()
        }
      ]}
    />
  </PageContainer>

TeamToolComponent.propTypes = {
  canEdit: T.bool.isRequired,
  teamParams: T.shape(TeamParamsType.propTypes).isRequired,
  resourceTypes: T.arrayOf(T.string).isRequired,
  workspaceId: T.string.isRequired,
  resetForm: T.func.isRequired,
  openCurrentTeam: T.func.isRequired,
  resetCurrentTeam: T.func.isRequired,
  openMultipleTeamsForm: T.func.isRequired,
  resetMultipleTeamsForm: T.func.isRequired
}

const TeamTool = connect(
  (state) => ({
    canEdit: selectors.canEdit(state),
    teamParams: selectors.teamParams(state),
    resourceTypes: selectors.resourceTypes(state),
    workspaceId: workspaceSelect.workspace(state).uuid
  }),
  (dispatch) => ({
    resetForm(formData) {
      dispatch(formActions.resetForm('teamParamsForm', formData))
    },
    openCurrentTeam(id, teamParams, workspaceId, resourceTypes) {
      const defaultValue = {
        id: makeId(),
        workspace: {
          uuid: workspaceId
        },
        selfRegistration: teamParams.selfRegistration,
        selfUnregistration: teamParams.selfUnregistration,
        publicDirectory: teamParams.publicDirectory,
        deletableDirectory: teamParams.deletableDirectory,
        creatableResources: resourceTypes
      }
      dispatch(actions.openForm('teams.current', id, defaultValue))
    },
    resetCurrentTeam() {
      dispatch(formActions.resetForm('teams.current', {}, true))
    },
    openMultipleTeamsForm(teamParams, resourceTypes) {
      const defaultValue = {
        nbTeams: 1,
        selfRegistration: teamParams.selfRegistration,
        selfUnregistration: teamParams.selfUnregistration,
        publicDirectory: teamParams.publicDirectory,
        deletableDirectory: teamParams.deletableDirectory,
        creatableResources: resourceTypes
      }
      dispatch(formActions.resetForm('teams.multiple', defaultValue, true))
    },
    resetMultipleTeamsForm() {
      dispatch(formActions.resetForm('teams.multiple', {}, true))
    }
  })
)(TeamToolComponent)

export {
  TeamTool
}