import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'

import {ImportFile as ImportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'
import {Logs} from '#/main/transfer/tools/transfer/log/components/logs'

const ImportDetails = props =>
  <TransferDetails
    transferFile={props.importFile}
    actions={[
      {
        name: 'download',
        className: 'btn-emphasis',
        type: URL_BUTTON,
        label: trans('download', {}, 'actions'),
        target: get(props.importFile, 'file.url'),
        disabled: !get(props.importFile, 'file.url'),
        primary: true
      }
    ]}
  >
    <Logs />
  </TransferDetails>

ImportDetails.propTypes = {
  importFile: T.shape(
    ImportFileTypes.propTypes
  )
}

export {
  ImportDetails
}
