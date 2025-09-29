import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Dashboard} from '#/main/app/dashboard'

import {selectors} from '#/main/evaluation/sequence/store'
import {SequencePage} from '#/main/evaluation/sequence/components/page'
import {SequenceDashboardEvaluation} from '#/main/evaluation/sequence/dashboard/evaluation/components/main'
import {SequenceDashboardActions} from '#/main/evaluation/sequence/dashboard/components/actions'
import {SequenceDashboardOverview} from '#/main/evaluation/sequence/dashboard/overview/components/main'
import {SequenceDashboardStats} from '#/main/evaluation/sequence/dashboard/components/stats'

const SequenceDashboard = () => {
  const sequencePath = useSelector(selectors.path)

  return (
    <SequencePage title={trans('dashboard')}>
      <Dashboard
        path={sequencePath+'/dashboard'}
        overviewPage={SequenceDashboardOverview}
        statsPage={SequenceDashboardStats}
        actionsPage={SequenceDashboardActions}
        pages={[
          {
            name: 'results',
            icon: 'fa fa-award',
            title: trans('evaluation'),
            component: SequenceDashboardEvaluation
          }
        ]}
      />
    </SequencePage>
  )
}

export {
  SequenceDashboard
}
