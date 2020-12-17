import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Flagged} from '#/plugin/forum/resources/forum/moderation/components/flagged'
import {Blocked} from '#/plugin/forum/resources/forum/moderation/components/blocked'

const Moderation = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/moderation/flagged',
        render: () => {
          const component = <Flagged path={props.path} />

          return component
        }
      }, {
        path: '/moderation/blocked',
        render: () => {
          const component = <Blocked path={props.path} />

          return component
        }
      }
    ]}
  />

Moderation.propTypes = {
  path: T.string.isRequired
}

export {
  Moderation
}
