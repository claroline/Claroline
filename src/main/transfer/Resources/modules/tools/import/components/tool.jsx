import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'

import {TransferForm} from '#/main/transfer/components/form'
import {ImportList} from '#/main/transfer/tools/import/containers/list'
import {ImportForm} from '#/main/transfer/tools/import/containers/form'
import {ImportDetails} from '#/main/transfer/tools/import/containers/details'

const ImportTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-transfer-transfer-tool']}
    pages={[
      {
        path: '/',
        exact: true,
        component: ImportList
      }, {
        path: '/new',
        disabled: !props.canImport,
        render: () => (
          <TransferForm
            path={props.path+'/new'}
            title={trans('import', {}, 'transfer')}
            explanation={props.explanation}
            openForm={props.openForm}
            contextData={props.contextData}
          >
            <ImportForm />
          </TransferForm>
        )
      }, {
        path: '/:id',
        onEnter: (params) => props.open(params.id),
        component: ImportDetails
      }
    ]}
  />

ImportTool.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  explanation: T.object,
  canImport: T.bool.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  ImportTool
}
