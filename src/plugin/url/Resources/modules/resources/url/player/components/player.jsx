import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UrlDisplay} from '#/plugin/url/components/display'
import {ResourcePage} from '#/main/core/resource'

const Player = (props) =>
  <ResourcePage>
    <UrlDisplay
      url={props.url.url}
      mode={props.url.mode}
      ratio={props.url.ratio}
    />
  </ResourcePage>

Player.propTypes = {
  url: T.shape({
    id: T.number.isRequired,
    url: T.string.isRequired,
    mode: T.string.isRequired,
    ratio: T.number
  }).isRequired
}

export {
  Player
}
