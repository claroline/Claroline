import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {LinkedOrganizations} from '#/main/community/components/linked-organizations'
import {actions, selectors} from '#/main/app/context/editor'
import {selectors as workspaceSelectors} from '#/main/app/contexts/workspace/editor/store'

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
          description: trans('workspace_organizations_desc', {}, 'workspace'),
          primary: true,
          render: () => (
            <LinkedOrganizations
              autoload={!!context && !!context.id}
              name={`${workspaceSelectors.STORE_NAME}.organizations`}
              description={trans('workspace_organizations_desc', {}, 'workspace')}
              url={['apiv2_workspace_list_organizations', {id: context ? context.id : null}]}
              addUrl={['apiv2_workspace_add_organizations', {id: context ? context.id : null}]}
              removeUrl={['apiv2_workspace_remove_organizations', {id: context ? context.id : null}]}
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
