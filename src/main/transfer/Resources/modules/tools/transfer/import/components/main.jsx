import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'

import {TransferForm} from '#/main/transfer/tools/transfer/components/form'

import {ImportForm} from '#/main/transfer/tools/transfer/import/containers/form'
import {ImportList} from '#/main/transfer/tools/transfer/import/containers/list'
import {ImportDetails} from '#/main/transfer/tools/transfer/import/containers/details'

const ImportMain = (props) =>
  <Routes
    path={props.path+'/import'}
    redirect={[
      {from: '/', exact: true, to: '/new', disabled: !props.canImport},
      {from: '/', exact: true, to: '/history', disabled: props.canImport}
    ]}
    routes={[
      {
        path: '/history',
        exact: true,
        component: ImportList
      }, {
        path: '/history/:id',
        component: ImportDetails,
        onEnter: (params) => props.open(params.id)
      }, {
        path: '/new',
        disabled: !props.canImport,
        render: () => (
          <TransferForm
            path={props.path+'/import/new'}
            title={trans('import', {}, 'transfer')}
            explanation={props.explanation}
            openForm={props.openForm}
            contextData={props.contextData}
          >
            <ImportForm />
          </TransferForm>
        )
      }
    ]}
  />

ImportMain.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  explanation: T.object,
  canImport: T.bool.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  ImportMain
}
