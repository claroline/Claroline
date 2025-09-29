import React from 'react'
import {useSelector} from 'react-redux'

import {PageContent, PageSection} from '#/main/app/page'
import {ProgressionChart} from '#/main/evaluation/chart/components/progression'
import {ScoreChart} from '#/main/evaluation/chart/components/score'
import {StatusChart} from '#/main/evaluation/chart/components/status'

import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'
import {selectors} from '#/main/evaluation/sequence/dashboard/store'

const SequenceDashboardStats = () => {
  const sequence = useSelector(sequenceSelectors.sequence)
  const hasScore = useSelector(sequenceSelectors.hasScore)
  const totalScore = useSelector(sequenceSelectors.totalScore)
  const successScore = useSelector(sequenceSelectors.successScore)

  return (
    <PageContent className="pt-4">
      <PageSection size="full">
        <div className="row">
          <div className="col-6">
            <StatusChart name={selectors.STORE_NAME+'.statuses'} url={['apiv2_sequence_evaluation_status', {id: sequence.id}]} />
          </div>
        </div>
      </PageSection>

      <PageSection size="full" className="mb-5">
        <ProgressionChart name={selectors.STORE_NAME+'.completion'} url={['apiv2_sequence_evaluation_completion', {id: sequence.id}]} />
      </PageSection>

      {hasScore &&
        <PageSection size="full" className="mb-5">
          <ScoreChart
            className="border-top pt-5"
            name={selectors.STORE_NAME+'.scores'}
            url={['apiv2_sequence_evaluation_scores', {id: sequence.id}]}
            totalScore={totalScore}
            successScore={successScore}
          />
        </PageSection>
      }
    </PageContent>
  )
}

export {
  SequenceDashboardStats
}
