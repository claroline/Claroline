import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Nav} from '#/main/app/components/nav'
import {PageContent, PageSection} from '#/main/app/page'
import {selectors as toolSelectors} from '#/main/core/tool'
import {selectors as dashboardSelectors} from '#/main/core/tool/dashboard/store'

import {EvaluationDashboardWorkspaces} from '#/main/evaluation/tools/evaluation/dashboard/components/workspaces'
import {EvaluationDashboardResources} from '#/main/evaluation/tools/evaluation/dashboard/components/resources'
import {EvaluationDashboardSequences} from '#/main/evaluation/tools/evaluation/dashboard/components/sequences'

const EvaluationDashboardEvaluations = () => {
  const dashboardPath = useSelector(dashboardSelectors.path)
  const contextType = useSelector(toolSelectors.contextType)

  return (
    <PageContent className="py-4">
      <Nav
        className="nav-justified content-lg mb-4 px-4"
        variant="bar"
        orientation="horizontal"
        items={[
          {
            name: 'workspaces',
            type: LINK_BUTTON,
            label: trans('desktop' === contextType ? 'workspaces':'workspace'),
            target: `${dashboardPath}/results`,
            exact: true
          }, {
            name: 'sequences',
            type: LINK_BUTTON,
            label: trans('sequences', {}, 'evaluation'),
            target: `${dashboardPath}/results/sequences`
          }, {
            name: 'resources',
            type: LINK_BUTTON,
            label: trans('resources'),
            target: `${dashboardPath}/results/resources`
          }
        ]}
      />

      <PageSection size="full" className="d-flex flex-fill">
        <Routes
          path={dashboardPath+'/results'}
          routes={[
            {
              path: '/',
              exact: true,
              component: EvaluationDashboardWorkspaces
            }, {
              path: '/sequences',
              component: EvaluationDashboardSequences
            }, {
              path: '/resources',
              component: EvaluationDashboardResources
            }
          ]}
        />
      </PageSection>
    </PageContent>
  )
}

export {
  EvaluationDashboardEvaluations
}
