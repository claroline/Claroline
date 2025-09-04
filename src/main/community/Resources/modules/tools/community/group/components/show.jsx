import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details'
import {Alert} from '#/main/app/components/alert'
import {PageSection} from '#/main/app/page/components/section'
import {PageTabbedSection} from '#/main/app/page/components/tabbed-section'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_ROLES} from '#/main/community/modals/roles'
import {RoleList} from '#/main/community/role/components/list'

import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {GroupPage} from '#/main/community/group/components/page'
import {GroupActivity} from '#/main/community/group/components/activity'
import {constants} from '#/main/community/constants'

const GroupShow = (props) =>
  <GroupPage
    path={props.path}
    group={props.group}
    reload={props.reload}
  >
    <PageSection className="mb-5">
      {hasPermission('administrate', props.group) && get(props.group, 'meta.readOnly') &&
        <Alert type="info" className="my-3">{trans('group_locked', {}, 'community')}</Alert>
      }

      <DetailsData
        data={props.group}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'code',
                type: 'string',
                label: trans('code')
              }
            ]
          }
        ]}
      />
    </PageSection>

    <PageTabbedSection
      className="embedded-list-section"
      tabs={[
        {
          name: 'activity',
          title: trans('activity'),
          render: () => (
            <GroupActivity group={props.group} />
          )
        }, {
          name: 'users',
          title: trans('users', {}, 'community'),
          render: () => (
            <>
              {hasPermission('administrate', props.group) && !get(props.group, 'meta.everyone', false) ?
                <Button
                  className="btn btn-primary mt-4 me-auto"
                  {...{
                    name: 'add-users',
                    type: MODAL_BUTTON,
                    // icon: 'fa fa-fw fa-plus',
                    label: trans('add_users', {}, 'actions'),
                    disabled: get(props.group, 'meta.readOnly'),
                    modal: [MODAL_USERS, {
                      selectAction: (selected) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addUsers(props.group.id, selected)
                      })
                    }]
                  }}
                /> :
                <Alert type="info" className="mt-4 mb-0">
                  {trans('group_everyone_help', {}, 'community')}
                </Alert>
              }

              <UserList
                className="mt-4 mb-5"
                path={props.path}
                name={`${selectors.FORM_NAME}.users`}
                url={['apiv2_group_list_users', {id: props.group.id}]}
                autoload={!!props.group.id}
                delete={{
                  url: ['apiv2_group_remove_users', {id: props.group.id}],
                  icon: 'fa fa-fw fa-times',
                  label: trans('unregister', {}, 'actions'),
                  displayed: () => !get(props.group, 'meta.everyone', false) && hasPermission('administrate', props.group)
                }}
                actions={undefined}
              />
            </>
          )
        }, {
          name: 'roles',
          title: trans('roles', {}, 'community'),
          displayed: hasPermission('administrate', props.group),
          render: () => (
            <>
              {!get(props.group, 'meta.readOnly', false) &&
                <Button
                  className="btn btn-primary mt-4 me-auto"
                  {...{
                    name: 'add-roles',
                    type: MODAL_BUTTON,
                    // icon: 'fa fa-fw fa-plus',
                    label: trans('add_roles', {}, 'actions'),
                    disabled: get(props.group, 'meta.readOnly'),
                    modal: [MODAL_ROLES, {
                      personal: false,
                      selectAction: (selected) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addRoles(props.group.id, selected)
                      })
                    }]
                  }}
                />
              }

              <RoleList
                className="mt-4 mb-5"
                path={props.path}
                name={`${selectors.FORM_NAME}.roles`}
                url={['apiv2_group_list_roles', {id: props.group.id}]}
                autoload={!!props.group.id}
                delete={{
                  url: ['apiv2_group_remove_roles', {id: props.group.id}],
                  icon: 'fa fa-fw fa-times',
                  label: trans('remove', {}, 'actions'),
                  disabled: () => get(props.group, 'meta.readOnly')
                }}
                actions={undefined}
                customDefinition={[
                  {
                    name: 'type',
                    type: 'choice',
                    label: trans('type'),
                    options: {
                      choices: constants.ROLE_TYPES
                    },
                    displayed: true
                  }, {
                    name: 'workspace',
                    type: 'workspace',
                    label: trans('workspace'),
                    displayed: true
                  }
                ]}
              />
            </>
          )
        }
      ]}
    />
  </GroupPage>

GroupShow.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  group: T.shape(
    GroupTypes.propTypes
  ),
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addRoles: T.func.isRequired
}

export {
  GroupShow
}
