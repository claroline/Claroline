import React from 'react'

import {TabbedPageContainer} from '#/main/core/layout/page/containers/tabbed-page.jsx'
import {t} from '#/main/core/translation'

import {Import as ImportTab} from '#/main/core/administration/transfer/components/import/tab.jsx'
//import {Export as ExportTab} from '#/main/core/administration/transfer/components/export/tab.jsx'
import {Action as ImportAction} from '#/main/core/administration/transfer/components/import/action.jsx'
//import {Action as ExportAction} from '#/main/core/administration/transfer/components/export/action.jsx'

const Transfer = () =>
 <TabbedPageContainer
  redirect={[
    {from: '/', exact: true, to: '/import'}
  ]}

  tabs={[
    {
      icon: 'fa fa-save',
      title: t('import'),
      path: '/import',
      actions: ImportAction,
      content: ImportTab
    }/*, {
      icon: 'fa fa-download',
      title: t('export'),
      path: '/export',
      actions: ExportAction,
      content: ExportTab
    }*/
  ]}
/>

export {
  Transfer
}
