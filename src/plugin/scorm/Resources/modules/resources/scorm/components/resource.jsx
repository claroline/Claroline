import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Resource, ResourcePage} from '#/main/core/resource'

import {Scorm as ScormType} from '#/plugin/scorm/resources/scorm/prop-types'
import {Player} from '#/plugin/scorm/resources/scorm/player/containers/player'
import {Editor} from '#/plugin/scorm/resources/scorm/editor/components/editor'
import {Results} from '#/plugin/scorm/resources/scorm/player/components/results'
import {ScormSummary} from '#/plugin/scorm/resources/scorm/containers/summary'

const ScormResource = props =>
  <Resource {...omit(props, 'scorm', 'editable', 'resetForm')}>
    <ResourcePage
      customActions={[
        {
          name: 'play',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-play',
          label: trans('start', {}, 'actions'),
          target: `${props.path}/play`,
          exact: true
        }, {
          name: 'show-results',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('show-results', {}, 'actions'),
          displayed: props.editable,
          target: `${props.path}/results`,
          exact: true
        }, {
          name: 'export-results',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-results', {}, 'actions'),
          displayed: props.editable,
          file: {
            url: ['apiv2_scormscotracking_export', {scorm: props.scorm.id}]
          },
          group: trans('transfer')
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
          path: '/summary',
          component: ScormSummary
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
  </Resource>

ScormResource.propTypes = {
  path: T.string.isRequired,
  scorm: T.shape(ScormType.propTypes),
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

export {
  ScormResource
}
