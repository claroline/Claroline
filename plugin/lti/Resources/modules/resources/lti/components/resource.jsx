import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {LtiResource as LtiResourceType} from '#/plugin/lti/resources/lti/prop-types'
import {Player} from '#/plugin/lti/resources/lti/player/components/player'
import {Editor} from '#/plugin/lti/resources/lti/editor/components/editor'

const LtiResource = props =>
  <ResourcePage>
    <Routes
      key="resource-content"
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/play'}
      ]}
      routes={[
        {
          path: '/play',
          component: Player
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.editable,
          onLeave: () => props.resetForm(),
          onEnter: () => props.resetForm(props.ltiResource)
        }
      ]}
    />
  </ResourcePage>

LtiResource.propTypes = {
  path: T.string.isRequired,
  ltiResource: T.shape(LtiResourceType.propTypes),
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

export {
  LtiResource
}
