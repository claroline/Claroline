import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ContentRights} from '#/main/app/content/components/rights'
import {EditorPage} from '#/main/app/editor'
import {trans} from '#/main/app/intl'
import {getTool} from '#/main/core/tool/utils'

const ToolEditorPermissions = (props) => {
  const [permissions, setPermissions] = useState({})

  useEffect(() => {
    // load tool configuration to get the list of implemented permissions
    getTool(props.name, props.contextType).then((toolModule) => {
      setPermissions(toolModule.default.permissions)
    })

    // load tool rights
    props.loadRights(props.name, props.contextType, get(props.contextData, 'id'))
  }, [props.name, props.contextType, get(props.contextData, 'id')])

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('permissions_help')}
      managerOnly={true}
      definition={[
        {
          name: 'public',
          title: trans('public'),
          primary: true,
          fields: [
            {
              name: 'data.meta.public',
              type: 'boolean',
              label: trans('make_tool_public', {}, 'tools'),
              help: 'workspace' === props.contextType ? [
                trans('make_tool_public_workspace_help', {}, 'tools'),
                trans('make_tool_public_workspace_warning', {}, 'tools')
              ] : trans('make_tool_public_help', {}, 'tools')
            }
          ]
        }, {
          name: 'roles',
          icon: 'fa fa-fw fa-id-badges',
          title: trans('roles'),
          description: trans('Assignez des permissions aux rôles pour personnaliser les droits des utilisateurs possédant ce rôle.'),
          primary: true,
          render: () => props.rights && (
            <ContentRights
              workspace={props.contextData}
              permissions={permissions}
              rights={props.rights}
              updateRights={props.updateRights}
            />
          )
        }
      ]}
    />
  )
}

ToolEditorPermissions.propTypes = {
  name: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  loadRights: T.func.isRequired,
  updateRights: T.func.isRequired
}

export {
  ToolEditorPermissions
}
