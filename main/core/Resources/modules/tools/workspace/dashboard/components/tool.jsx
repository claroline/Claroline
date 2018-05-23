import React from 'react'
import {trans} from '#/main/core/translation'
import {
  PageContainer,
  PageContent,
  PageHeader
} from '#/main/core/layout/page'
import { Dashboard } from '#/main/core/tools/workspace/dashboard/components/dashboard'


const Tool = () =>
  <PageContainer>
    <PageHeader title={trans('dashboard', {}, 'tools')} />
    <PageContent>
      <Dashboard/>
    </PageContent>
  </PageContainer>

export {
  Tool as DashboardTool
}
