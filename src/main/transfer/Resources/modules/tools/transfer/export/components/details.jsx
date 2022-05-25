import React from 'react'
import {PropTypes as T} from 'prop-types'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {ExportFile as ExportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'

const ExportDetails = props =>
  <TransferDetails
    transferFile={props.exportFile}
    actions={[
      {
        name: 'download',
        className: 'btn-emphasis',
        type: URL_BUTTON,
        label: trans('download', {}, 'actions'),
        target: ['apiv2_transfer_export_download', {id: props.exportFile ? props.exportFile.id : null}],
        disabled: !props.exportFile || 'success' !== props.exportFile.status,
        primary: true
      }, {
        name: 'refresh',
        type: CALLBACK_BUTTON,
        label: trans('refresh-data', {}, 'actions'),
        callback: () => props.refresh(props.exportFile.id),
        disabled: !props.exportFile || !hasPermission('edit', props.exportFile)
      }
    ]}
  />

ExportDetails.propTypes = {
  exportFile: T.shape(
    ExportFileTypes.propTypes
  ),
  refresh: T.func.isRequired
}

export {
  ExportDetails
}
