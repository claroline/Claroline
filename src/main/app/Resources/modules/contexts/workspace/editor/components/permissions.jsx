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
        name: 'organizations',
        title: trans('organizations'),
        primary: true,
        fields: [
          {
            name: 'organizations',
            label: trans('organizations'),
            type: 'organizations'
          }
        ]
      }
    ]}
  />

export {
  WorkspaceEditorPermissions
}
