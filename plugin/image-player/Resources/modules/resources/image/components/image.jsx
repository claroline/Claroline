import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {copyToClipboard} from '#/main/core/copy-text-to-clipboard'
import {ResourceContainer} from '#/main/core/layout/resource/containers/resource.jsx'

import {select} from './../selectors'

const Image = props =>
  <ResourceContainer
    customActions={[
      {
        icon: 'fa fa-fw fa-clipboard',
        label: t('copy_permalink_to_clipboard'),
        action: () => copyToClipboard(props.url)
      }
    ]}
  >
    <div className="text-center">
      <img src={props.url} alt={props.hashName} />
    </div>
  </ResourceContainer>

Image.propTypes = {
  url: T.string.isRequired,
  hashName: T.string.isRequired
}

function mapStateToProps(state) {
  return {
    url: select.url(state),
    hashName: select.hashName(state)
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedImage = connect(mapStateToProps, mapDispatchToProps)(Image)

export {
  ConnectedImage as Image
}
