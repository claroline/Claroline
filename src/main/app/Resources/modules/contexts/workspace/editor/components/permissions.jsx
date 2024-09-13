import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const WorkspaceEditorPermissions = () =>
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
      },{
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
      }
    ]}
  />

export {
  WorkspaceEditorPermissions
}
