import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool'

import {route} from '#/main/community/user/routing'
import {UserCard} from '#/main/community/user/components/card'
import {constants} from '#/main/community/constants'
import {selectors} from '#/main/community/tools/community/pending/store'
import {PageListSection} from '#/main/app/page/components/list-section'

const PendingMain = props =>
  <ToolPage
    title={trans('pending_registrations')}
  >
    <PageListSection>
      <ListData
        flush={true}
        name={selectors.LIST_NAME}
        fetch={{
          url: ['apiv2_workspace_list_pending', {id: props.workspace.id}],
          autoload: true
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: route(row, props.path)
        })}
        actions={(rows) => [{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-check',
          label: trans('accept', {}, 'actions'),
          callback: () => props.register(rows, props.workspace),
          confirm: {
            title: trans('user_registration'),
            message: trans('workspace_user_register_validation_message', {users: rows.map(user => user.username).join(',')})
          }
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-ban',
          label: trans('decline', {}, 'actions'),
          callback: () => props.remove(rows, props.workspace),
          confirm: {
            title: trans('user_remove'),
            message: trans('workspace_user_remove_validation_message', {users: rows.map(user => user.username).join(',')})
          }
        }]}
        definition={[
          {
            name: 'username',
            type: 'username',
            label: trans('username'),
            displayed: true,
            primary: true
          }, {
            name: 'lastName',
            type: 'string',
            label: trans('last_name'),
            displayed: true
          }, {
            name: 'firstName',
            type: 'string',
            label: trans('first_name'),
            displayed: true
          }, {
            name: 'email',
            alias: 'mail',
            type: 'email',
            label: trans('email'),
            displayed: true
          }, {
            name: 'administrativeCode',
            type: 'string',
            label: trans('code')
          }, {
            name: 'lastActivity',
            type: 'date',
            label: trans('last_activity'),
            displayed: true,
            options: {
              time: true
            }
          }, {
            name: 'roles',
            alias: 'role',
            type: 'roles',
            label: trans('roles'),
            calculated: (user) => !isEmpty(props.workspace) ?
              user.roles.filter(role => role.workspace && role.workspace.id === props.workspace.id)
              :
              user.roles.filter(role => constants.ROLE_PLATFORM === role.type),
            displayed: true,
            filterable: true
          }
        ]}
        card={UserCard}
      />
    </PageListSection>
  </ToolPage>

PendingMain.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  register: T.func,
  remove: T.func
}

export {
  PendingMain
}
