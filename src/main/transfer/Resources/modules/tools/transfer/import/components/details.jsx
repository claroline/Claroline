import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ImportFile as ImportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'
import {Logs} from '#/main/transfer/tools/transfer/log/components/logs'

const ImportDetails = props =>
  <TransferDetails
    transferFile={props.importFile}
    downloadUrl={get(props.importFile, 'file.url')}
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
