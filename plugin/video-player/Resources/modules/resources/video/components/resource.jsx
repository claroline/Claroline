import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {generateUrl} from '#/main/core/api/router'
import {trans} from '#/main/core/translation'
import {copyToClipboard} from '#/main/core/copy-text-to-clipboard'
import {select as resourceSelect} from '#/main/core/resource/selectors'
import {RoutedPageContent} from '#/main/core/layout/router'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {Player} from '#/plugin/video-player/resources/video/player/components/player.jsx'
import {MODAL_VIDEO_SUBTITLES} from '#/plugin/video-player/resources/video/editor/components/modal/subtitles.jsx'

const Resource = props => {
  const routes = [
    {
      path: '/play',
      component: Player
    }
  ]
  const redirect = [{
    from: '/',
    to: '/play',
    exact: true
  }]
  const customActions = [
    {
      icon: 'fa fa-fw fa-list',
      label: trans('subtitles'),
      displayed: props.canEdit,
      action: () => props.showModal(MODAL_VIDEO_SUBTITLES)
    },{
      icon: 'fa fa-fw fa-download',
      label: trans('download'),
      displayed: props.canDownload,
      action: () => window.location = generateUrl('claro_resource_download') + '?ids[]=' + props.resource.autoId
    }, {
      icon: 'fa fa-fw fa-clipboard',
      label: trans('copy_permalink_to_clipboard'),
      action: () => copyToClipboard(props.url)
    }
  ]

  return (
    <ResourcePageContainer
      customActions={customActions}
    >
      <RoutedPageContent
        headerSpacer={false}
        redirect={redirect}
        routes={routes}
      />
    </ResourcePageContainer>
  )
}

Resource.propTypes = {
  resource: T.shape({
    id: T.string.isRequired,
    autoId: T.number.isRequired
  }).isRequired,
  url: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canDownload: T.bool.isRequired,
  showModal: T.func.isRequired
}

const VideoPlayerResource = connect(
  state => ({
    resource: state.resourceNode,
    url: state.url,
    canEdit: resourceSelect.editable(state),
    canDownload: resourceSelect.exportable(state)
  }),
  (dispatch) => ({
    showModal: (type) => dispatch(modalActions.showModal(type, {}))
  })
)(Resource)

export {
  VideoPlayerResource
}