import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {copy} from '#/main/app/clipboard'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {CALLBACK_BUTTON, DOWNLOAD_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {Player} from '#/plugin/video-player/resources/video/player/components/player'
import {MODAL_VIDEO_SUBTITLES} from '#/plugin/video-player/resources/video/editor/components/modal/subtitles'

const Resource = props =>
  <ResourcePageContainer
    customActions={[
      {
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('subtitles'),
        displayed: props.canEdit,
        modal: [MODAL_VIDEO_SUBTITLES]
      },{ // todo should be a resource action
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('download'),
        displayed: props.canDownload,
        file: {
          url: url(['claro_resource_download'], {ids: [props.resource.autoId]})
        }
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-clipboard',
        label: trans('copy_permalink_to_clipboard'),
        callback: () => copy(props.url)
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={true}
      redirect={[
        {from: '/', exact: true, to: '/play'}
      ]}
      routes={[
        {
          path: '/play',
          component: Player
        }
      ]}
    />
  </ResourcePageContainer>

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
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canDownload: hasPermission('export', resourceSelect.resourceNode(state))
  })
)(Resource)

export {
  VideoPlayerResource
}
