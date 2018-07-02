import React from 'react'
import {connect} from 'react-redux'
import {Reported} from '#/plugin/blog/resources/blog/moderation/components/reported.jsx'
import {Unpublished} from '#/plugin/blog/resources/blog/moderation/components/unpublished.jsx'
import {Routes} from '#/main/app/router'

const ModerationComponent = () =>
  <div>
    moderation WIP
    <Routes
      routes={[
        {
          path: '/moderation/reported',
          component: Reported
        }, {
          path: '/moderation/unpublished',
          component: Unpublished
        }
      ]}
    />
  </div>


ModerationComponent.propTypes = {
}

const Moderation = connect(
  () => ({
  })
)(ModerationComponent)

export {Moderation}