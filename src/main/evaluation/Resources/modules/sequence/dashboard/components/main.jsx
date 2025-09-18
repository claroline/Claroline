import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Dashboard} from '#/main/app/dashboard'

import {selectors} from '#/main/evaluation/sequence/store'
import {SequencePage} from '#/main/evaluation/sequence/components/page'
import {SequenceDashboardActivity} from '#/main/evaluation/sequence/dashboard/components/activity'
import {SequenceDashboardEvaluations} from '#/main/evaluation/sequence/dashboard/components/evaluations'
import {SequenceDashboardActions} from '#/main/evaluation/sequence/dashboard/components/actions'
import {SequenceDashboardOverview} from '#/main/evaluation/sequence/dashboard/components/overview'
// import {SequenceDashboardStats} from '#/main/evaluation/sequence/dashboard/components/stats'

const SequenceDashboard = () => {
  const sequencePath = useSelector(selectors.path)

  return (
    <SequencePage title={trans('dashboard')}>
      <Dashboard
        path={sequencePath+'/dashboard'}
        overviewPage={SequenceDashboardOverview}
        // statsPage={SequenceDashboardStats}
        activityPage={SequenceDashboardActivity}
        actionsPage={SequenceDashboardActions}
        pages={[
          {
            name: 'results',
            icon: 'fa fa-award',
            title: trans('evaluation'),
            component: SequenceDashboardEvaluations
          }
        ]}
      />
    </SequencePage>
  )
}

export {
  SequenceDashboard
}
