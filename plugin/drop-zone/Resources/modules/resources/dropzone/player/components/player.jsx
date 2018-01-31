import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/core/router/components/router.jsx'

const Player = props =>
  <Routes
    routes={[
      {
        path: '/submit',
        component: Editor
      }, {
        path: '/review'
      }
    ]}
  />

Player.propTypes = {

}

export {
  Player
}
