import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {actions as formActions} from '#/main/core/data/form/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {select} from '#/plugin/path/resources/path/selectors'
import {Overview} from '#/plugin/path/resources/path/overview/components/overview.jsx'
import {Editor} from '#/plugin/path/resources/path/editor/components/editor.jsx'
import {Player} from '#/plugin/path/resources/path/player/components/player.jsx'

const Resource = props =>
  <ResourcePageContainer
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: props.path.display.showOverview,
        target: '/',
        exact: true
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: '/play'
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={false}
      routes={[
        {
          path: '/edit',
          component: Editor,
          disabled: !props.editable,
          onEnter: () => props.resetForm(props.path)
        }, {
          path: '/play',
          component: Player
        }, {
          path: '/',
          exact: true,
          component: Overview,
          disabled: !props.path.display.showOverview
        }
      ]}
      redirect={[
        // redirect to player when no overview
        {
          disabled: props.path.display.showOverview,
          from: '/',
          to: '/play',
          exact: true
        }
      ]}
    />
  </ResourcePageContainer>

Resource.propTypes = {
  path: T.object.isRequired,
  editable: T.bool.isRequired,

  resetForm: T.func.isRequired
}

const PathResource = connect(
  (state) => ({
    path: select.path(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  (dispatch) => ({
    resetForm(data) {
      dispatch(formActions.resetForm('pathForm', data, false))
    }
  })
)(Resource)

export {
  PathResource
}
