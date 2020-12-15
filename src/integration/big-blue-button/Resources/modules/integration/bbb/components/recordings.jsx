import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {Recordings} from '#/integration/big-blue-button/components/recordings'
import {selectors} from '#/integration/big-blue-button/integration/bbb/store/selectors'

const BBBRecordings = (props) =>
  <Fragment>
    <ContentTitle
      level={2} title={trans('recordings', {}, 'bbb')}
      actions={[
        {
          name: 'sync',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-sync',
          label: trans('sync_recordings', {}, 'bbb'),
          callback: () => props.syncRecordings()
        }
      ]}
    />

    <Recordings
      name={selectors.STORE_NAME+'.recordings'}
      url={['apiv2_bbb_integration_recordings_list']}
      delete={['apiv2_bbb_integration_recordings_delete']}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open', {}, 'actions'),
        target: resourceRoute(row.meeting)
      })}
      customDefinition={[
        {
          name: 'meeting.name',
          label: trans('resource'),
          displayed: true,
          primary: true,
          order: 1
        }
      ]}
    />
  </Fragment>

BBBRecordings.propTypes = {
  syncRecordings: T.func.isRequired
}

export {
  BBBRecordings
}
