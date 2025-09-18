import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Nav} from '#/main/app/components/nav'
import {Routes} from '#/main/app/router'
import {PageContent, PageSection} from '#/main/app/page'

import {selectors} from '#/main/evaluation/sequence/dashboard/store'
import {SequenceDashboardSequence} from '#/main/evaluation/sequence/dashboard/components/sequence'
import {SequenceDashboardResources} from '#/main/evaluation/sequence/dashboard/components/resources'

const SequenceDashboardEvaluations = () => {
  const dashboardPath = useSelector(selectors.path)

  return (
    <PageContent className="py-4">
      <Nav
        className="nav-justified content-lg mb-4 px-4"
        variant="bar"
        orientation="horizontal"
        items={[
          {
            name: 'sequence',
            type: LINK_BUTTON,
            label: trans('sequence', {}, 'evaluation'),
            target: `${dashboardPath}/results`,
            exact: true
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
              component: SequenceDashboardSequence
            }, {
              path: '/resources',
              component: SequenceDashboardResources
            }
          ]}
        />
      </PageSection>
    </PageContent>
  )
}

export {
  SequenceDashboardEvaluations
}
