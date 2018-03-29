import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {copyToClipboard} from '#/main/core/copy-text-to-clipboard'
import {PageContent} from '#/main/core/layout/page'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {select as resourceSelect} from '#/main/core/resource/selectors'
import {select} from './../selectors'

const Image = props =>
  <ResourcePageContainer
    customActions={[
      {
        icon: 'fa fa-fw fa-clipboard',
        label: t('copy_permalink_to_clipboard'),
        action: () => copyToClipboard(props.url)
      }
    ]}
  >
    <PageContent className="text-center">
      <img src={props.url} alt={props.hashName} onContextMenu={(e)=>{checkDownload(e, props.exportable)}}/>
    </PageContent>
  </ResourcePageContainer>

Image.propTypes = {
  url: T.string.isRequired,
  hashName: T.string.isRequired,
  exportable: T.bool.isRequired
}

function mapStateToProps(state) {
  return {
    url: select.url(state),
    hashName: select.hashName(state),
    exportable: resourceSelect.exportable(state)
  }
}

function checkDownload(e, exportable) {
  if (!exportable) {
    e.preventDefault()
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedImage = connect(mapStateToProps, mapDispatchToProps)(Image)

export {
  ConnectedImage as Image
}
