import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {BBB as BBBTypes} from '#/integration/big-blue-button/resources/bbb/prop-types'
import {Player} from '#/integration/big-blue-button/resources/bbb/player/containers/player'
import {End} from '#/integration/big-blue-button/resources/bbb/player/components/end'
import {Records} from '#/integration/big-blue-button/resources/bbb/records/containers/records'
import {BBBEditor} from '#/integration/big-blue-button/resources/bbb/editor/components/main'

const BBBResource = props =>
  <Resource
    {...omit(props)}
    menu={[
      {
        name: 'records',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-video',
        label: trans('recordings', {}, 'bbb'),
        target: props.path+'/records',
        displayed: props.allowRecords && props.bbb.record
      }
    ]}
    actions={[
      {
        name: 'close',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-door-closed',
        label: trans('end_meeting', {}, 'bbb'),
        displayed: props.canEdit,
        callback: () => props.endMeeting(props.bbb.id),
        group: trans('management'),
        dangerous: true
      }
    ]}
    editor={BBBEditor}
    pages={[
      {
        path: '/',
        component: Player,
        exact: true
      }, {
        path: '/end',
        component: End,
        exact: true
      }, {
        path: '/records',
        component: Records,
        disabled: !props.allowRecords || !props.bbb.record
      }
    ]}
  />

BBBResource.propTypes = {
  path: T.string.isRequired,
  bbb: T.shape(
    BBBTypes.propTypes
  ).isRequired,
  allowRecords: T.bool,
  canEdit: T.bool.isRequired,
  resetForm: T.func.isRequired,
  endMeeting: T.func.isRequired
}

export {
  BBBResource
}
