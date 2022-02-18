import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'

import {TransferForm} from '#/main/transfer/tools/transfer/components/form'

import {ExportForm} from '#/main/transfer/tools/transfer/export/containers/form'
import {ExportList} from '#/main/transfer/tools/transfer/export/containers/list'
import {ExportDetails} from '#/main/transfer/tools/transfer/export/containers/details'

const ExportMain = (props) =>
  <Routes
    path={props.path+'/export'}
    redirect={[
      {from: '/', exact: true, to: '/new'}
    ]}
    routes={[
      {
        path: '/history',
        exact: true,
        component: ExportList
      }, {
        path: '/history/:id',
        component: ExportDetails,
        onEnter: (params) => props.open(params.id)
      }, {
        path: '/new',
        render: () => (
          <TransferForm
            path={props.path+'/export/new'}
            title={trans('export', {}, 'transfer')}
            explanation={props.explanation}
            openForm={props.openForm}
            contextData={props.contextData}
          >
            <ExportForm />
          </TransferForm>
        )
      }
    ]}
  />

ExportMain.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  explanation: T.object,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  ExportMain
}
