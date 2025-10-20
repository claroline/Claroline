import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'

import {actions, selectors} from '#/main/app/context/editor'
import {actions as workspaceActions, selectors as workspaceSelectors} from '#/main/app/contexts/workspace/editor/store'

import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'
import {OrganizationList} from '#/main/community/organization/components/list'

// easy selection for restrictions
const restrictByDates = (workspace) => get(workspace, 'data.restrictions.enableDates') || !isEmpty(get(workspace, 'data.restrictions.dates'))
const restrictByCode  = (workspace) => get(workspace, 'data.restrictions.enableCode') || !!get(workspace, 'data.restrictions.code')

const WorkspaceEditorPermissions = () => {
  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(actions.update(value, 'data.'+prop))
  }

  const context = useSelector(selectors.contextData)

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('permissions_help')}
      managerOnly={true}
      definition={[
        {
          name: 'public',
          title: trans('public_workspace', {}, 'workspace'),
          primary: true,
          fields: [
            {
              name: 'data.meta.public',
              type: 'boolean',
              label: trans('make_workspace_public', {}, 'workspace'),
              help: [
                trans('make_workspace_public_help', {}, 'workspace'),
                trans('make_workspace_public_warning', {}, 'workspace')
              ]
            }
          ]
        }, {
          name: 'organizations',
          title: trans('organizations', {}, 'community'),
          description: trans('Choisissez les organisations dans lesquels l\'espace d\'activités doit apparaître. Seuls les membres de ces organisations pourront voir et s\'inscrire à l\'espace.'),
          primary: true,
          render: () => (
            <OrganizationList
              className="mb-3"
              name={`${workspaceSelectors.STORE_NAME}.organizations`}
              url={['apiv2_workspace_list_organizations', {id: context ? context.id : null}]}
              autoload={!!context && !!context.id}
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
                    callback: () => dispatch(workspaceActions.addOrganizations(context.id, organizations.map(organization => organization.id)))
                  })
                }]
              }}
              delete={{
                url: ['apiv2_workspace_remove_organizations', {id: context ? context.id : null}]
              }}
              actions={() => []}
            />
          )
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          description: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
          primary: true,
          fields: [
            {
              name: 'data.restrictions.enableDates',
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
                  name: 'data.restrictions.dates',
                  type: 'date-range',
                  label: trans('access_dates'),
                  displayed: restrictByDates,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'data.restrictions.enableCode',
              label: trans('restrict_by_code'),
              help: trans('restrict_by_code_help'),
              type: 'boolean',
              calculated: restrictByCode,
              onChange: activated => {
                if (!activated) {
                  updateProp('restrictions.code', '')
                }
              },
              linked: [
                {
                  name: 'data.restrictions.code',
                  label: trans('access_code'),
                  displayed: restrictByCode,
                  type: 'password',
                  required: true,
                  autoComplete: 'off',
                  options: {
                    disablePasswordCheck: true
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
  WorkspaceEditorPermissions
}
