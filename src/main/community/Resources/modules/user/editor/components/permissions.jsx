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
import {OrganizationList} from '#/main/community/organization/components/list'
import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'
import {constants} from '#/main/community/constants'

// easy selection for restrictions
const restrictByDates = (workspace) => get(workspace, 'restrictions.enableDates') || !isEmpty(get(workspace, 'restrictions.dates'))

const UserEditorPermissions = () => {
  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
  }

  const currentUser = useSelector(selectors.user)

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
              url={['apiv2_user_list_roles', {id: currentUser.id}]}
              autoload={!!currentUser.id}
              addAction={{
                name: 'add-roles',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_roles', {}, 'actions'),
                tooltip: 'bottom',
                modal: [MODAL_ROLES, {
                  selectAction: (selected) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => dispatch(actions.addRoles(currentUser.id, selected.map(role => role.id)))
                  })
                }]
              }}
              delete={{
                url: ['apiv2_user_remove_roles', {id: currentUser.id}]
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
          description: trans('Choisissez les organisations auxquels l\'utilisateur a accès.'),
          primary: true,
          render: () => (
            <OrganizationList
              /*path={props.path}*/
              className="mb-3"
              name={`${selectors.STORE_NAME}.organizations`}
              url={['apiv2_user_list_organizations', {id: currentUser.id}]}
              autoload={!!currentUser.id}
              addAction={{
                name: 'add',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_organizations', {}, 'actions'),
                tooltip: 'bottom',
                modal: [MODAL_ORGANIZATIONS, {
                  selectAction: (organizations) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => dispatch(actions.addOrganizations(currentUser.id, organizations.map(organization => organization.id)))
                  })
                }]
              }}
              delete={{
                url: ['apiv2_user_remove_organizations', {id: currentUser.id}]
              }}
              actions={() => []}
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
