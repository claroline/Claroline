import React from 'react'
import {PropTypes as T} from 'prop-types'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ExportFile as ExportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'
import {ExportForm} from '#/main/transfer/tools/transfer/export/containers/form'

const ExportDetails = props =>
  <TransferDetails
    path={props.exportFile ? props.path+'/export/history/'+props.exportFile.id : ''}
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
  >
    {props.exportFile &&
      <Routes
        path={props.path+'/export/history/'+props.exportFile.id}
        routes={[
          {
            path: '/edit',
            component: ExportForm,
            onEnter: () => props.openForm(props.exportFile)
          }
        ]}
      />
    }
  </TransferDetails>

ExportDetails.propTypes = {
  path: T.string.isRequired,
  exportFile: T.shape(
    ExportFileTypes.propTypes
  ),
  refresh: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  ExportDetails
}
