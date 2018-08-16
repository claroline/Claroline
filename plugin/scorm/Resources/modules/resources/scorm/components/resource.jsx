import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {hasPermission} from '#/main/core/resource/permissions'
import {RoutedPageContent} from '#/main/core/layout/router/components/page'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Scorm as ScormType} from '#/plugin/scorm/resources/scorm/prop-types'
import {select} from '#/plugin/scorm/resources/scorm/selectors'
import {Player} from '#/plugin/scorm/resources/scorm/player/components/player'
import {Editor} from '#/plugin/scorm/resources/scorm/editor/components/editor'
import {Results} from '#/plugin/scorm/resources/scorm/player/components/results'

const Resource = props =>
  <ResourcePage
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
    <RoutedPageContent
      key="resource-content"
      headerSpacer={true}
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

Resource.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  editable: T.bool.isRequired,
  resetForm: T.func.isRequired
}

const ScormResource = connect(
  (state) => ({
    scorm: select.scorm(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  (dispatch) => ({
    resetForm(formData) {
      dispatch(formActions.resetForm('scormForm', formData))
    }
  })
)(Resource)

export {
  ScormResource
}
