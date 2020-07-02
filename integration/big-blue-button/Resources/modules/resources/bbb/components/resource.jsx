import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {BBB as BBBTypes} from '#/integration/big-blue-button/resources/bbb/prop-types'
import {Player} from '#/integration/big-blue-button/resources/bbb/player/containers/player'
import {End} from '#/integration/big-blue-button/resources/bbb/player/components/end'
import {Editor} from '#/integration/big-blue-button/resources/bbb/editor/containers/editor'
import {Records} from '#/integration/big-blue-button/resources/bbb/records/containers/records'

const BBBResource = props =>
  <ResourcePage
    customActions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-stop',
        label: trans('end_meeting', {}, 'bbb'),
        displayed: props.canEdit,
        callback: () => props.endMeeting(props.bbb.id),
        group: trans('management')
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-video',
        label: trans('show-records', {}, 'actions'),
        target: props.path+'/records',
        displayed: props.allowRecords && props.bbb.record
      }
    ]}
    routes={[
      {
        path: '/',
        component: Player,
        exact: true
      }, {
        path: '/edit',
        component: Editor,
        disabled: !props.canEdit,
        onEnter: () => props.resetForm(props.bbb),
        onLeave: () => props.resetForm()
      }, {
        path: '/end',
        component: End,
        exact: true
      }, {
        path: '/records',
        component: Records,
        onEnter: () => props.loadRecordings(props.bbb.id),
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
  endMeeting: T.func.isRequired,
  loadRecordings: T.func.isRequired
}

export {
  BBBResource
}
