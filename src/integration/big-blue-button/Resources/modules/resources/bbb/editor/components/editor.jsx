import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/integration/big-blue-button/resources/bbb/constants'
import {selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {BBB as BBBTypes} from '#/integration/big-blue-button/resources/bbb/prop-types'

const Editor = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME+'.bbbForm'}
    buttons={true}
    target={(bbb) => ['apiv2_bbb_update', {id: bbb.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        id: 'general',
        icon: 'fa fa-fw fa-cogs',
        title: trans('general'),
        primary: true,
        help: props.bbb.runningOn ? trans('running_on_server', {server: props.bbb.runningOn}, 'bbb') : undefined,
        fields: [
          {
            name: 'record',
            type: 'boolean',
            label: trans('allow_recording', {}, 'bbb'),
            displayed: props.allowRecords
          }, {
            name: 'customUsernames',
            type: 'boolean',
            label: trans('allow_custom_usernames', {}, 'bbb')
          }
        ]
      }, {
        id: 'display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'newTab',
            type: 'boolean',
            label: trans('open_meeting_in_new_tab', {}, 'bbb'),
            linked: [
              {
                name: 'ratioList',
                type: 'choice',
                label: trans('display_ratio_list'),
                options: {
                  multiple: false,
                  condensed: false,
                  choices: constants.DISPLAY_RATIO_LIST
                },
                displayed: (bbb) => !bbb.newTab,
                onChange: (ratio) => props.updateProp('ratio', parseFloat(ratio))
              }, {
                name: 'ratio',
                type: 'number',
                label: trans('display_ratio'),
                options: {
                  min: 0,
                  unit: '%'
                },
                displayed: (bbb) => !bbb.newTab,
                onChange: () => props.updateProp('ratioList', null)
              }
            ]
          }
        ]
      }, {
        id: 'messages',
        icon: 'fa fa-fw fa-commenting',
        title: trans('messages'),
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
              props.allowRecords && props.bbb.record ? trans('force_server_help_records', {}, 'bbb') : undefined
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
                  choices: props.servers.reduce((acc, server) => Object.assign({}, acc, {
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

Editor.propTypes = {
  path: T.string.isRequired,
  bbb: T.shape(
    BBBTypes.propTypes
  ),
  servers: T.arrayOf(T.shape({
    url: T.string.isRequired
  })),
  allowRecords: T.bool,
  updateProp: T.func.isRequired
}

export {
  Editor
}