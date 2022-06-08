import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'

import {Routes} from '#/main/app/router'

import {ImportFile as ImportFileTypes} from '#/main/transfer/prop-types'
import {TransferDetails} from '#/main/transfer/tools/transfer/components/details'
import {ImportForm} from '#/main/transfer/tools/transfer/import/containers/form'
import {Logs} from '#/main/transfer/tools/transfer/log/components/logs'

const ImportDetails = props =>
  <TransferDetails
    path={props.importFile ? props.path+'/import/history/'+props.importFile.id : ''}
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
    {props.importFile &&
      <Routes
        path={props.path+'/import/history/'+props.importFile.id}
        routes={[
          {
            path: '/',
            exact: true,
            component: Logs
          }, {
            path: '/edit',
            component: ImportForm,
            onEnter: () => props.openForm(props.importFile)
          }
        ]}
      />
    }
  </TransferDetails>

ImportDetails.propTypes = {
  path: T.string.isRequired,
  importFile: T.shape(
    ImportFileTypes.propTypes
  ),
  openForm: T.func.isRequired
}

export {
  ImportDetails
}
