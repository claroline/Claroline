import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {TeamParams as TeamParamsType} from '#/plugin/team/tools/team/prop-types'
import {Editor} from '#/plugin/team/tools/team/containers/editor'
import {Teams} from '#/plugin/team/tools/team/containers/teams'
import {Team} from '#/plugin/team/tools/team/containers/team'
import {TeamForm} from '#/plugin/team/tools/team/containers/team-form'
import {MultipleTeamForm} from '#/plugin/team/tools/team/containers/multiple-team-form'

const TeamTool = props =>
  <ToolPage
    actions={[
      {
        name: 'team-create',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_a_team', {}, 'team'),
        target: `${props.path}/team/form`,
        primary: true,
        displayed: props.canEdit
      }, {
        name: 'team-params',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('configure'),
        target: `${props.path}/edit`,
        displayed: props.canEdit
      }, {
        name: 'teams-create',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-user-plus',
        label: trans('create_teams', {}, 'team'),
        target: `${props.path}/teams/multiple/form`,
        displayed: props.canEdit
      }, {
        name: 'home',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('home'),
        target: `${props.path}/teams`,
        exact: true
      }
    ]}
  >
    <Routes
      path={props.path}
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
          onEnter: (params) => props.openCurrentTeam(params.id, props.teamParams, props.workspaceId, props.resourceTypes),
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
  </ToolPage>

TeamTool.propTypes = {
  path: T.string.isRequired,
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

export {
  TeamTool
}