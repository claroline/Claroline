import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {select} from '#/plugin/path/resources/path/selectors'
import {Overview} from '#/plugin/path/resources/path/overview/components/overview'
import {Editor} from '#/plugin/path/resources/path/editor/components/editor'
import {Player} from '#/plugin/path/resources/path/player/components/player'

const Resource = props =>
  <ResourcePageContainer
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: props.path.display.showOverview,
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
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
          disabled: !props.editable
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
  editable: T.bool.isRequired
}

const PathResource = connect(
  (state) => ({
    path: select.path(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state))
  })
)(Resource)

export {
  PathResource
}
