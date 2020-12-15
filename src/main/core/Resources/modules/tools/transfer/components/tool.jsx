import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {ImportTab} from '#/main/core/tools/transfer/import/containers/tab'
import {History as HistoryTab} from '#/main/core/tools/transfer/history/components/tab'

const TransferTool = (props) =>
  <ToolPage
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/import',  render: () => trans('import')},
          {path: '/history', render: () => trans('history')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/import'}
      ]}
      routes={[
        {
          path: '/import',
          component: ImportTab
        }, {
          path: '/history',
          component: HistoryTab
        }
      ]}
    />
  </ToolPage>

TransferTool.propTypes = {
  path: T.string.isRequired
}

export {
  TransferTool
}
