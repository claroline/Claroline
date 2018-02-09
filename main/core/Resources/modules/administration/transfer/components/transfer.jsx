import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {Import as ImportTab} from '#/main/core/administration/transfer/components/import/tab.jsx'
//import {Export as ExportTab} from '#/main/core/administration/transfer/components/export/tab.jsx'
import {Action as ImportAction} from '#/main/core/administration/transfer/components/import/action.jsx'
//import {Action as ExportAction} from '#/main/core/administration/transfer/components/export/action.jsx'

const Transfer = () =>
  <TabbedPageContainer
    title={trans('data_transfer', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/import'}
    ]}

    tabs={[
      {
        icon: 'fa fa-save',
        title: trans('import'),
        path: '/import',
        actions: ImportAction,
        content: ImportTab
      }/*, {
        icon: 'fa fa-download',
        title: trans('export'),
        path: '/export',
        actions: ExportAction,
        content: ExportTab
      }*/
    ]}
  />

export {
  Transfer
}
