import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'

import {BBB as BBBTypes} from '#/integration/big-blue-button/resources/bbb/prop-types'
import {selectors} from '#/integration/big-blue-button/resources/bbb/records/store/selectors'
import {Recordings} from '#/integration/big-blue-button/components/recordings'

const Records = props =>
  <Fragment>
    <ContentTitle
      level={2}
      title={trans('recordings', {}, 'bbb')}
      backAction={{
        type: LINK_BUTTON,
        target: props.path+'/',
        exact: true
      }}
    />

    <Recordings
      name={selectors.LIST_NAME}
      url={['apiv2_bbb_meeting_recordings_list', {id: props.bbb.id}]}
      delete={['apiv2_bbb_meeting_recording_delete', {id: props.bbb.id}]}
      primaryAction={(row) => ({
        type: URL_BUTTON,
        label: trans('open', {}, 'actions'),
        target: get(row, 'medias.presentation', '')
      })}
      customDefinition={[]}
    />
  </Fragment>

Records.propTypes = {
  path: T.string.isRequired,
  bbb: T.shape(
    BBBTypes.propTypes
  )
}

export {
  Records
}