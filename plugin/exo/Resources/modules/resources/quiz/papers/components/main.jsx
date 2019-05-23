import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Papers} from '#/plugin/exo/resources/quiz/papers/components/papers'
import {Paper}  from '#/plugin/exo/resources/quiz/papers/components/paper'

const PapersMain = props =>
  <Routes
    routes={[
      {
        path: '/papers',
        exact: true,
        component: Papers
      }, {
        path: '/papers/:id',
        component: Paper,
        onEnter: (params) => {
          props.loadCurrentPaper(props.quizId, params.id)

          if (props.showStatistics) { // TODO : replace by the one in the paper structure
            props.statistics(props.quizId)
          }
        },
        onLeave: props.resetCurrentPaper
      }
    ]}
  />

PapersMain.propTypes = {
  quizId: T.string.isRequired,
  showStatistics: T.bool.isRequired,
  statistics: T.func.isRequired,
  loadCurrentPaper: T.func.isRequired,
  resetCurrentPaper: T.func.isRequired
}

export {
  PapersMain
}
