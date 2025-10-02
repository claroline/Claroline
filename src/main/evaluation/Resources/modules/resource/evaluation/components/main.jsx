import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {Nav} from '#/main/app/components/nav'
import {PageContent, PageHeading, PageSection} from '#/main/app/page'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as dashboardSelectors} from '#/main/core/resource/dashboard/store'

import {ResourceDashboardEvaluations} from '#/main/evaluation/resource/evaluation/components/evaluations'
import {ResourceDashboardAttempts} from '#/main/evaluation/resource/evaluation/components/attempts'

const ResourceDashboardEvaluation = () => {
  const dashboardPath = useSelector(dashboardSelectors.path)

  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const hasAttempts = useSelector(resourceSelectors.hasAttempts)

  return (
    <PageContent className="py-4">
      <PageHeading title={trans('evaluation')} className="visually-hidden" level={2} />

      {hasAttempts &&
        <Nav
          className="nav-justified content-lg mb-4 px-4"
          variant="bar"
          orientation="horizontal"
          items={[
            {
              name: 'evaluations',
              type: LINK_BUTTON,
              label: trans(get(resourceNode, 'meta.type', 'resource'), {}, 'resource'),
              target: `${dashboardPath}/results`,
              exact: true
            }, {
              name: 'attempts',
              type: LINK_BUTTON,
              label: trans('attempts', {}, 'evaluation'),
              target: `${dashboardPath}/results/attempts`
            }
          ]}
        />
      }

      <PageSection size="full" className="d-flex flex-fill">
        <Routes
          path={dashboardPath+'/results'}
          routes={[
            {
              path: '/',
              exact: true,
              component: ResourceDashboardEvaluations
            }, {
              path: '/attempts',
              disabled: !hasAttempts,
              component: ResourceDashboardAttempts
            }
          ]}
        />
      </PageSection>
    </PageContent>
  )
}

export {
  ResourceDashboardEvaluation
}
