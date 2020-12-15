import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/team/tools/team/store'

const Teams = props =>
  <ListData
    name={selectors.STORE_NAME + '.teams.list'}
    primaryAction={(row) => ({
      type: 'link',
      label: trans('open'),
      target: `${props.path}/teams/${row.id}`
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
        target: `${props.path}/team/form/${rows[0].id}`
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

Teams.propTypes = {
  path: T.string.isRequired,
  workspaceId: T.string.isRequired,
  myTeams: T.arrayOf(T.string),
  canEdit: T.bool.isRequired,
  selfRegister: T.func.isRequired,
  selfUnregister: T.func.isRequired,
  fillTeams: T.func.isRequired,
  emptyTeams: T.func.isRequired
}

export {
  Teams
}
