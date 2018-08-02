import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {copy} from '#/main/app/clipboard'
import {PageContent} from '#/main/core/layout/page'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {select} from '#/plugin/image-player/resources/image/selectors'

const ImageComponent = props =>
  <ResourcePageContainer
    customActions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-clipboard',
        label: trans('copy_permalink_to_clipboard'),
        callback: () => copy(props.url)
      }
    ]}
  >
    <PageContent className="text-center content-centered">
      <img
        className="img-responsive"
        src={props.url}
        alt={props.hashName}
        onContextMenu={(e)=> {
          if (!props.exportable) {
            e.preventDefault()
          }
        }}
      />
    </PageContent>
  </ResourcePageContainer>

ImageComponent.propTypes = {
  url: T.string.isRequired,
  hashName: T.string.isRequired,
  exportable: T.bool.isRequired
}

const Image = connect(
  (state) => ({
    url: select.url(state),
    hashName: select.hashName(state),
    exportable: hasPermission('export', resourceSelect.resourceNode(state))
  })
)(ImageComponent)

export {
  Image
}
