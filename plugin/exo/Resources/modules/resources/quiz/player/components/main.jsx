import React from 'react'

import {Routes} from '#/main/app/router'

import {PlayerStep} from '#/plugin/exo/resources/quiz/player/components/step'
import {PlayerEnd} from '#/plugin/exo/resources/quiz/player/components/end'

const PlayerMain = () =>
  <Routes
    routes={[
      {
        path: '/:id',
        component: PlayerStep
      }, {
        path: '/end',
        component: PlayerEnd
      }
    ]}
  />

PlayerMain.propTypes = {

}

export {
  PlayerMain
}
