import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UrlDisplay} from '#/plugin/url/components/display'

const Player = (props) =>
  <UrlDisplay
    url={props.url.url}
    mode={props.url.mode}
    ratio={props.url.ratio}
  />

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
