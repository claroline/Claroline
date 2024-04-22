import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Flagged} from '#/plugin/forum/resources/forum/moderation/components/flagged'
import {Blocked} from '#/plugin/forum/resources/forum/moderation/components/blocked'

const Moderation = (props) =>
  <Routes
    path={props.path+'/moderation'}
    routes={[
      {
        path: '/flagged',
        render: () => {
          const component = <Flagged path={props.path} />

          return component
        }
      }, {
        path: '/blocked',
        render: () => {
          console.log('coucou')
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
