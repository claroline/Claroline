import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {selectors} from '#/integration/big-blue-button/resources/bbb/store'
const BBBEditorParameters = () => {
  const servers = useSelector(selectors.servers)
  const allowRecords = useSelector(selectors.allowRecords)

  const editedResource = useSelector(editorSelectors.resource)
  
  return (
    <EditorPage
      title={trans('parameters')}
      dataPart="resource"
      definition={[
        {
          id: 'general',
          icon: 'fa fa-fw fa-cogs',
          title: trans('general'),
          primary: true,
          help: editedResource.runningOn ? trans('running_on_server', {server: editedResource.runningOn}, 'bbb') : undefined,
          fields: [
            {
              name: 'record',
              type: 'boolean',
              label: trans('allow_recording', {}, 'bbb'),
              displayed: allowRecords
            }, {
              name: 'customUsernames',
              type: 'boolean',
              label: trans('allow_custom_usernames', {}, 'bbb')
            }
          ]
        }, {
          id: 'messages',
          icon: 'fa fa-fw fa-commenting',
          title: trans('messages'),
          primary: true,
          fields: [
            {
              name: 'welcomeMessage',
              type: 'html',
              label: trans('welcome_message', {}, 'bbb')
            }, {
              name: 'endMessage',
              type: 'html',
              label: trans('end_message', {}, 'bbb')
            }
          ]
        }, {
          id: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          primary: true,
          fields: [
            {
              name: 'restrictions.disabled',
              type: 'boolean',
              label: trans('disable'),
              help: trans('meeting_disabled_help', {}, 'bbb')
            }, {
              name: 'moderatorRequired',
              type: 'boolean',
              label: trans('wait_for_moderator', {}, 'bbb'),
              help: trans('moderator_help_message', {}, 'bbb')
            }, {
              name: 'forceServer',
              type: 'boolean',
              label: trans('force_server', {}, 'bbb'),
              calculated: (bbb) => bbb.forceServer || get(bbb, 'restrictions.server'),
              help: [
                trans('force_server_help', {}, 'bbb'),
                trans('force_server_help2', {}, 'bbb'),
                allowRecords && editedResource.record ? trans('force_server_help_records', {}, 'bbb') : undefined
              ].filter(value => !!value),
              linked: [
                {
                  name: 'restrictions.server',
                  type: 'choice',
                  label: trans('server', {}, 'bbb'),
                  displayed: (bbb) => bbb.forceServer || get(bbb, 'restrictions.server'),
                  required: true,
                  options: {
                    condensed: false,
                    choices: servers.reduce((acc, server) => Object.assign({}, acc, {
                      [server.url]: server.url
                    }), {})
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
  BBBEditorParameters
}
