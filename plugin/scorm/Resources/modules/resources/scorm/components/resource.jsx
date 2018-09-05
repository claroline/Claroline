import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Scorm as ScormType} from '#/plugin/scorm/resources/scorm/prop-types'
import {Player} from '#/plugin/scorm/resources/scorm/player/components/player'
import {Editor} from '#/plugin/scorm/resources/scorm/editor/components/editor'
import {Results} from '#/plugin/scorm/resources/scorm/player/components/results'

const ScormResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-scorm-resource']}
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('play_scorm', {}, 'scorm'),
        target: '/play',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('results_list', {}, 'scorm'),
        disabled: !props.editable,
        displayed: props.editable,
        target: '/results',
        exact: true
      }
    ]}
  >
    <Routes
      key="resource-content"
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
          onEnter: () => props.resetForm(props.scorm)
        }, {
          path: '/results',
          component: Results,
          disabled: !props.editable
        }
      ]}
    />
  </ResourcePage>

ScormResource.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

export {
  ScormResource
}
