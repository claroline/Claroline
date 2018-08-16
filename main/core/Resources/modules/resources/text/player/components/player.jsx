import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'

import {selectors} from '#/main/core/resources/text/store'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

const PlayerComponent = props =>
  <HtmlText>
    {props.text.content}
  </HtmlText>

PlayerComponent.propTypes = {
  text: T.shape(TextTypes.propTypes).isRequired
}

const Player = connect(
  state => ({
    text: selectors.text(state)
  })
)(PlayerComponent)

export {
  Player
}