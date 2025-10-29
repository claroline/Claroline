import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {actions as formActions} from '#/main/app/content/form'
import {EditorPage} from '#/main/app/editor'

import {selectors} from '#/main/community/user/editor/store'
import {RoleList} from '#/main/community/role/components/list'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_ROLES} from '#/main/community/modals/roles'
import {actions} from '#/main/community/user/editor/store'
import {constants} from '#/main/community/constants'
import {LinkedOrganizations} from '#/main/community/components/linked-organizations'

// easy selection for restrictions
const restrictByDates = (workspace) => get(workspace, 'restrictions.enableDates') || !isEmpty(get(workspace, 'restrictions.dates'))

const UserEditorPermissions = () => {
  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
  }

  const editedUser = useSelector(selectors.user)

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('Gérez les différents droits d\'accès et de modifications de l\'utilisateur.')}
      managerOnly={true}
      definition={[
        {
          name: 'roles',
          title: trans('roles'),
          primary: true,
          hideTitle: false,
          render: () => (
            <RoleList
              className="mb-3"
              name={`${selectors.STORE_NAME}.roles`}
              url={['apiv2_user_list_roles', {id: editedUser.id}]}
              autoload={!!editedUser.id}
              addAction={{
                name: 'add-roles',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_roles', {}, 'actions'),
                tooltip: 'bottom',
                modal: [MODAL_ROLES, {
                  personal: false,
                  selectAction: (selected) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => dispatch(actions.addRoles(editedUser.id, selected.map(role => role.id)))
                  })
                }]
              }}
              delete={{
                url: ['apiv2_user_remove_roles', {id: editedUser.id}],
                icon: 'fa fa-fw fa-times',
                label: trans('remove', {}, 'actions')
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
          )
        }, {
          name: 'organizations',
          title: trans('organizations', {}, 'community'),
          description: trans('user_organizations_desc', {}, 'community'),
          primary: true,
          render: () => (
            <LinkedOrganizations
              autoload={!!editedUser && !!editedUser.id}
              name={`${selectors.STORE_NAME}.organizations`}
              description={trans('user_organizations_desc', {}, 'community')}
              url={['apiv2_user_list_organizations', {id: editedUser.id}]}
              addUrl={['apiv2_user_add_organizations', {id: editedUser.id}]}
              removeUrl={['apiv2_user_remove_organizations', {id: editedUser.id}]}
            />
          )
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          description: trans('Ajoutez des conditions d\'accès supplémentaires à l\'utilisateur'),
          primary: true,
          fields: [
            {
              name: 'restrictions.enableDates',
              label: trans('restrict_by_dates'),
              help: trans('restrict_by_dates_help'),
              type: 'boolean',
              calculated: restrictByDates,
              onChange: activated => {
                if (!activated) {
                  updateProp('restrictions.dates', [])
                }
              },
              linked: [
                {
                  name: 'restrictions.dates',
                  type: 'date-range',
                  label: trans('access_dates'),
                  displayed: restrictByDates,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  UserEditorPermissions
}
