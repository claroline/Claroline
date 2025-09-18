import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ResourceAttempt} from '#/main/evaluation/resource/prop-types'
import {getAttempt} from '#/main/evaluation/resource/utils'
import {ContentLoader} from '#/main/app/content/components/loader'

const UserAttemptOverview = (props) => {
  const [attemptComponent, setAttemptComponent] = useState(null)

  useEffect(() => {
    if (get(props.evaluation, 'id')) {
      getAttempt(props.evaluation).then(attemptModule => {
        setAttemptComponent(attemptModule)
      })
    }
  }, [get(props.evaluation, 'id')])

  if (!attemptComponent || !attemptComponent.default) {
    return (
      <ContentLoader />
    )
  }

  return createElement(attemptComponent.default, {
    attempt: props.evaluation
  })
}

UserAttemptOverview.propTypes = {
  evaluation: T.shape(
    ResourceAttempt.propTypes
  )
}

export {
  UserAttemptOverview
}
