import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'
import {select as workspaceSelect} from '#/main/core/workspace/selectors'

import {selectors, actions} from '#/plugin/team/tools/team/store'

const TeamsComponent = props =>
  <ListData
    name="teams.list"
    primaryAction={(row) => ({
      type: 'link',
      label: trans('open'),
      target: `/teams/${row.id}`
    })}
    fetch={{
      url: ['apiv2_workspace_team_list', {workspace: props.workspaceId}],
      autoload: true
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        filterable: true,
        type: 'string',
        primary: true
      }, {
        name: 'selfRegistration',
        label: trans('public_registration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'selfUnregistration',
        label: trans('public_unregistration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'publicDirectory',
        alias: 'isPublic',
        label: trans('public_directory', {}, 'team'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'maxUsers',
        label: trans('max_users', {}, 'team'),
        displayed: true,
        filterable: true,
        type: 'number'
      },
      {
        name: 'countUsers',
        label: trans('registered_users', {}, 'platform'),
        displayed: true,
        filterable: false,
        type: 'number'
      }
    ]}
    delete={{
      url: ['apiv2_team_delete_bulk'],
      displayed: () => props.canEdit
    }}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        displayed: props.canEdit,
        scope: ['object'],
        target: `/team/form/${rows[0].id}`
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-sign-in',
        label: trans('self_register', {}, 'team'),
        displayed: rows[0].selfRegistration &&
          -1 === props.myTeams.findIndex(teamId => teamId === rows[0].id) &&
          (!rows[0].maxUsers || rows[0].maxUsers > rows[0].countUsers),
        scope: ['object'],
        callback: () => props.selfRegister(rows[0].id)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-sign-out',
        label: trans('self_unregister', {}, 'team'),
        displayed: rows[0].selfUnregistration && -1 < props.myTeams.findIndex(teamId => teamId === rows[0].id),
        scope: ['object'],
        callback: () => props.selfUnregister(rows[0].id)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-sign-in',
        label: trans('fill_teams', {}, 'team'),
        displayed: props.canEdit,
        callback: () => props.fillTeams(rows)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-sign-out',
        label: trans('empty_teams', {}, 'team'),
        displayed: props.canEdit,
        callback: () => props.emptyTeams(rows)
      }
    ]}
  />

TeamsComponent.propTypes = {
  workspaceId: T.string.isRequired,
  myTeams: T.arrayOf(T.string),
  canEdit: T.bool.isRequired,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired,
  fillTeams: T.func.isRequired,
  emptyTeams: T.func.isRequired
}

const Teams = connect(
  (state) => ({
    workspaceId: workspaceSelect.workspace(state).uuid,
    myTeams: selectors.myTeams(state),
    canEdit: selectors.canEdit(state)
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
    },
    fillTeams(teams) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('fill_teams', {}, 'team'),
        question: trans('fill_selected_teams_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.fillTeams(teams))
      }))
    },
    emptyTeams(teams) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('empty_teams', {}, 'team'),
        question: trans('empty_selected_teams_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.emptyTeams(teams))
      }))
    }
  })
)(TeamsComponent)

export {
  Teams
}
