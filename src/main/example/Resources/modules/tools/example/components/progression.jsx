import React, {Fragment} from 'react'
import random from 'lodash/random'

import {ContentTitle} from '#/main/app/content/components/title'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

const ExampleProgression = () =>
  <Fragment>
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

  </Fragment>

export {
  ExampleProgression
}
