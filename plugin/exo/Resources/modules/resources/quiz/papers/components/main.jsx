import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {PapersList} from '#/plugin/exo/resources/quiz/papers/components/list'
import {PapersDetails} from '#/plugin/exo/resources/quiz/papers/components/details'

const PapersMain = props =>
  <Routes
    routes={[
      {
        path: '/papers',
        exact: true,
        component: PapersList,
        disabled: !props.registeredUser
      }, {
        path: '/papers/:id',
        component: PapersDetails,
        disabled: !props.registeredUser,
        onEnter: (params) => {
          props.loadCurrentPaper(props.quizId, params.id)

          if (props.showStatistics) {
            props.statistics(props.quizId)
          }
        },
        onLeave: () => props.resetCurrentPaper()
      }
    ]}
  />

PapersMain.propTypes = {
  quizId: T.string.isRequired,
  registeredUser: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  statistics: T.func.isRequired,
  loadCurrentPaper: T.func.isRequired,
  resetCurrentPaper: T.func.isRequired
}

export {
  PapersMain
}
