import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/core/translation'
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
      url: ['apiv2_team_list', {workspace: props.workspaceId}],
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
        displayed: true,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'selfUnregistration',
        label: trans('public_unregistration'),
        displayed: true,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'publicDirectory',
        alias: 'isPublic',
        label: trans('public_directory', {}, 'team'),
        displayed: true,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'maxUsers',
        label: trans('max_users', {}, 'team'),
        displayed: true,
        filterable: true,
        type: 'number'
      }
    ]}
    delete={{
      url: ['apiv2_team_delete_bulk'],
      displayed: () => props.canEdit
    }}
    actions={(rows) => [
      {
        type: 'link',
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        displayed: props.canEdit,
        scope: ['object'],
        target: `/team/form/${rows[0].id}`
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-sign-in',
        label: trans('fill_teams', {}, 'team'),
        displayed: props.canEdit,
        callback: () => props.fillTeams(rows)
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-sign-out',
        label: trans('empty_teams', {}, 'team'),
        displayed: props.canEdit,
        callback: () => props.emptyTeams(rows)
      }
    ]}
  />

TeamsComponent.propTypes = {
  workspaceId: T.string.isRequired,
  canEdit: T.bool.isRequired,
  fillTeams: T.func.isRequired,
  emptyTeams: T.func.isRequired
}

const Teams = connect(
  (state) => ({
    workspaceId: workspaceSelect.workspace(state).uuid,
    canEdit: selectors.canEdit(state)
  }),
  (dispatch) => ({
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