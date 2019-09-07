import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Scorm as ScormType} from '#/plugin/scorm/resources/scorm/prop-types'
import {Player} from '#/plugin/scorm/resources/scorm/player/containers/player'
import {Editor} from '#/plugin/scorm/resources/scorm/editor/components/editor'
import {Results} from '#/plugin/scorm/resources/scorm/player/components/results'

const ScormResource = props =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('play_scorm', {}, 'scorm'),
        target: `${props.path}/play`,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('results_list', {}, 'scorm'),
        disabled: !props.editable,
        displayed: props.editable,
        target: `${props.path}/results`,
        exact: true
      }
    ]}
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

ScormResource.propTypes = {
  path: T.string.isRequired,
  scorm: T.shape(ScormType.propTypes),
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

export {
  ScormResource
}
