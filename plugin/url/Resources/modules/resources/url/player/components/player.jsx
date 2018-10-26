import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {selectors} from '#/plugin/url/resources/url/store'

const PlayerComponent = props => {
  if (props.url.mode === 'redirect') {
    window.location.href = props.url.url

    return
  }

  if (props.url.mode === 'tab') {
    window.open(props.url.url,'_blank')
  }

  return (
    <div
      className="content-container claro-iframe-content-container"
      style={props.url.ratio ?
        {
          position: 'relative',
          paddingBottom: `${props.url.ratio}%`
        } :
        {}
      }
    >
      <iframe
        className="claro-iframe"
        src={props.url.url}
      />
    </div>
  )
}

PlayerComponent.propTypes = {
  url: T.shape({
    'id': T.number.isRequired,
    'url': T.string.isRequired,
    'mode': T.string.isRequired,
    'ratio': T.number.isRequired
  }).isRequired
}

const Player = connect(
  state => ({
    url: selectors.url(state)
  })
)(PlayerComponent)

export {
  Player
}
