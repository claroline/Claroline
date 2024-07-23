import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'

import {TransferForm} from '#/main/transfer/components/form'
import {ExportList} from '#/main/transfer/tools/export/containers/list'
import {ExportForm} from '#/main/transfer/tools/export/containers/form'
import {ExportDetails} from '#/main/transfer/tools/export/containers/details'

const ExportTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-transfer-transfer-tool']}
    pages={[
      {
        path: '/',
        exact: true,
        component: ExportList
      }, {
        path: '/history/:id',
        onEnter: (params) => props.open(params.id),
        component: ExportDetails
      }, {
        path: '/new',
        disabled: !props.canExport,
        render: () => (
          <TransferForm
            path={props.path+'/new'}
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

ExportTool.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  explanation: T.object,
  canExport: T.bool.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  ExportTool
}
