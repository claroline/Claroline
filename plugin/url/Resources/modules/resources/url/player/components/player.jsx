import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {selectors} from '#/plugin/url/resources/url/store'

const PlayerComponent = props => {
  window.location.href = props.url.url
}

PlayerComponent.propTypes = {
  url: T.shape({
    'id': T.number.isRequired
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
