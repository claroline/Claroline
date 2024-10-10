import React from 'react'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {useDispatch} from 'react-redux'
import {actions} from '#/main/app/context/editor'

// easy selection for restrictions
const restrictByDates = (workspace) => get(workspace, 'data.restrictions.enableDates') || !isEmpty(get(workspace, 'data.restrictions.dates'))
const restrictByCode  = (workspace) => get(workspace, 'data.restrictions.enableCode') || !!get(workspace, 'data.restrictions.code')

const WorkspaceEditorPermissions = () => {
  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(actions.update(value, 'data.'+prop))
  }

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('Gérez les différents droits d\'accès et de modifications de vos utilisateurs.')}
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
                trans('make_workspace_public_help', {}, 'workspace')
              ]
            }
          ]
        }, {
          name: 'organizations',
          title: trans('organizations'),
          subtitle: trans('Choisissez les organisations dans lesquels l\'espace d\'activités doit apparaître. Seuls les membres de ces organisations pourront voir et s\'inscrire à l\'espace.'),
          primary: true,
          fields: [
            {
              name: 'organizations',
              label: trans('organizations'),
              type: 'organizations',
              hideLabel: true
            }
          ]
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          subtitle: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
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
