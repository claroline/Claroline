import React from 'react'
import random from 'lodash/random'

import {ContentTitle} from '#/main/app/content/components/title'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {EvaluationGauge} from '#/main/evaluation/components/gauge'
import {constants} from '#/main/evaluation/constants'

const ExampleProgression = () =>
  <>
    <ContentTitle title="Progress bars" />

    <div className="mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'learning'].map(type =>
        <ProgressBar key={type} className="mb-2" type={type} value={random(0, 100)} />
      )}
    </div>

    <div className="mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'learning'].map(type =>
        <ProgressBar key={type} className="mb-2" type={type} value={random(0, 100)} size="xs" />
      )}
    </div>

    <div className="mb-3 d-flex gap-3 align-items-lg-start">
      <EvaluationGauge
        status={constants.EVALUATION_STATUS_NOT_ATTEMPTED}
        size="xl"
      />

      <EvaluationGauge
        status={constants.EVALUATION_STATUS_INCOMPLETE}
        size="xl"
        progression={40}
      />

      <EvaluationGauge
        status={constants.EVALUATION_STATUS_PENDING}
        size="xl"
        progression={100}
      />

      <EvaluationGauge
        status={constants.EVALUATION_STATUS_PASSED}
        size="xl"
        progression={100}
      />

      <EvaluationGauge
        status={constants.EVALUATION_STATUS_FAILED}
        size="xl"
        progression={100}
      />
    </div>

  </>

export {
  ExampleProgression
}
