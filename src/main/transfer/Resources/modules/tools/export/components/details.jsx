import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {Alert} from '#/main/app/alert/components/alert'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ExportForm} from '#/main/transfer/tools/export/containers/form'

import {ExportFile as ExportFileTypes} from '#/main/transfer/tools/export/prop-types'
import {TransferDetails} from '#/main/transfer/components/details'


const ExportDetails = props =>
  <TransferDetails
    path={props.exportFile ? props.path+'/'+props.exportFile.id : ''}
    transferFile={props.exportFile}
    actions={[
      {
        name: 'download',
        size: 'lg',
        type: URL_BUTTON,
        label: trans('download', {}, 'actions'),
        target: ['apiv2_transfer_export_download', {id: props.exportFile ? props.exportFile.id : null}],
        disabled: !props.exportFile || 'success' !== props.exportFile.status,
        primary: true
      }, {
        name: 'refresh',
        type: CALLBACK_BUTTON,
        label: trans('refresh', {}, 'actions'),
        callback: () => props.refresh(props.exportFile.id),
        disabled: !props.exportFile || !hasPermission('refresh', props.exportFile) || 'in_progress' === props.exportFile.status
      }
    ]}
  >
    {!props.exportFile || 'in_progress' === props.exportFile.status &&
      <Alert type="info" style={{marginTop: 20}}>
        {trans('export_in_progress_help', {}, 'transfer')}
      </Alert>
    }

    {props.exportFile &&
      <Routes
        path={props.path+'/'+props.exportFile.id}
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
