import React from 'react'
import {PropTypes as T} from 'prop-types'

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
        fields: [
          {
            name: 'activated',
            type: 'boolean',
            label: trans('activate_meeting', {}, 'bbb')
          }, {
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
            name: 'moderatorRequired',
            type: 'boolean',
            label: trans('wait_for_moderator', {}, 'bbb')
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
  allowRecords: T.bool,
  updateProp: T.func.isRequired
}

export {
  Editor
}