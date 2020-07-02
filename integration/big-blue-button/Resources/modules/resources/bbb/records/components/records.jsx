import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Sections} from '#/main/app/content/components/sections'

import {
  BBB as BBBTypes,
  Recording as RecordingTypes
} from '#/integration/big-blue-button/resources/bbb/prop-types'
import {Record} from '#/integration/big-blue-button/resources/bbb/records/components/record'

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

    {0 === props.recordings.length &&
      <ContentPlaceholder
        size="lg"
        icon="fa fa-video"
        title={trans('no_record', {}, 'bbb')}
      />
    }

    <Sections
      level={3}
    >
      {props.recordings.map((record) =>
        <Record
          key={record.recordID}
          id={record.recordID}
          meetingId={props.bbb.id}
          canEdit={props.canEdit}
          recording={record}
          deleteRecording={props.deleteRecording}
        />
      )}
    </Sections>
  </Fragment>

Records.propTypes = {
  path: T.string.isRequired,
  bbb: T.shape(
    BBBTypes.propTypes
  ),
  canEdit: T.bool.isRequired,
  recordings: T.arrayOf(T.shape(
    RecordingTypes.propTypes
  )),
  deleteRecording: T.func.isRequired
}

export {
  Records
}