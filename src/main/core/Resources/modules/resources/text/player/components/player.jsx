import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/main/core/resources/text/store'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {ResourcePage} from '#/main/core/resource'

const PlayerComponent = props =>
  <ResourcePage>
    <ContentHtml>
      {props.text.content}
    </ContentHtml>
  </ResourcePage>

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