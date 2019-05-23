import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Questions}  from '#/plugin/exo/resources/quiz/correction/components/questions'
import {Answers}    from '#/plugin/exo/resources/quiz/correction/components/answers'

const CorrectionMain = props =>
  <Routes
    routes={[
      {
        path: '/correction',
        exact: true,
        component: Questions,
        onEnter: () => props.correction()
      }, {
        path: '/correction/:id',
        component: Answers,
        onEnter: (params = {}) => props.correction(params.id)
      }
    ]}
  />

CorrectionMain.propTypes = {
  correction: T.func.isRequired
}

export {
  CorrectionMain
}
