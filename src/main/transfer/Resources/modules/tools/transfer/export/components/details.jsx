import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {ExportFile as ExportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'

const ExportDetails = props =>
  <TransferDetails
    transferFile={props.exportFile}
    downloadUrl={props.exportFile ? url(['apiv2_transfer_export_download', {id: props.exportFile.id}]) : null}
  >
  </TransferDetails>

ExportDetails.propTypes = {
  exportFile: T.shape(
    ExportFileTypes.propTypes
  )
}

export {
  ExportDetails
}
