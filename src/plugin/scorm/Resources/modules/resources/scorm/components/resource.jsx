import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Player} from '#/plugin/scorm/resources/scorm/containers/player'
import {Results} from '#/plugin/scorm/resources/scorm/components/results'
import {ScormEditor} from '#/plugin/scorm/resources/scorm/editor/components/main'

const ScormResource = props =>
  <Resource
    {...omit(props, 'scorm', 'editable')}
    actions={[
      {
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
    editor={ScormEditor}
    overviewPage={Player}
    redirect={[
      {from: '/play', to: '/'} // for retro-compatibility for old-routes
    ]}
    pages={[
      {
        path: '/results',
        component: Results,
        disabled: !props.editable
      }
    ]}
  />

ScormResource.propTypes = {
  path: T.string.isRequired,
  scorm: T.shape({
    id: T.string
  }),
  editable: T.bool.isRequired
}

export {
  ScormResource
}
