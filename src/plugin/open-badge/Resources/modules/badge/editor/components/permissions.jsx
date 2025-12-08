import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {actions, selectors} from '#/plugin/open-badge/badge/editor/store'
import {LinkedOrganizations} from '#/main/community/components/linked-organizations'

const BadgeEditorPermissions = () => {
  const dispatch = useDispatch()

  const badge = useSelector(selectors.data)

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('permissions_help')}
      managerOnly={true}
      definition={[
        {
          name: 'workspace',
          title: trans('workspace', {}, 'workspace'),
          description: trans('badge_workspace_desc', {}, 'badge'),
          primary: true,
          hideTitle: false,
          displayed: (badge) => !!badge.workspace,
          fields: [
            {
              name: 'workspace',
              label: trans('workspace', {}, 'workspace'),
              type: 'workspace',
              hideLabel: true,
              disabled: true,
              required: true,
              options: {
                multiple: false
              }
            }
          ]
        }, {
          name: 'organizations',
          title: trans('organizations', {}, 'community'),
          description: trans('badge_organizations_desc', {}, 'workspace'),
          primary: true,
          hideTitle: false,
          displayed: (badge) => !badge.workspace,
          render: () => badge && (
            <LinkedOrganizations
              autoload={!!badge && !!badge.id}
              name={`${selectors.STORE_NAME}.organizations`}
              description={trans('workspace_organizations_desc', {}, 'workspace')}
              url={['apiv2_badge_list_organizations', {id: badge ? badge.id : null}]}
              addUrl={['apiv2_badge_add_organizations', {id: badge ? badge.id : null}]}
              removeUrl={['apiv2_badge_remove_organizations', {id: badge ? badge.id : null}]}
            />
          )
        }, {
          id: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          description: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
          primary: true,
          fields: [
            {
              name: '_restrictDuration',
              type: 'boolean',
              label: trans('restrict_duration', {}, 'badge'),
              calculated: (badge) => badge._restrictDuration || !!badge.duration,
              onChange: (enabled) => {
                if (!enabled) {
                  dispatch(actions.update(null, 'duration'))
                }
              },
              linked: [
                {
                  name: 'duration',
                  type: 'number',
                  label: trans('duration'),
                  required: true,
                  displayed: (badge) => badge._restrictDuration || !!badge.duration,
                  options: {
                    unit: trans('days')
                  }
                }
              ]
            }, {
              name: 'restrictions.hideRecipients',
              type: 'boolean',
              label: trans('hide_recipients', {}, 'badge')
            }
          ]
        }
      ]}
    />
  )
}

export {
  BadgeEditorPermissions
}
