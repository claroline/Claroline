import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourceMenu} from '#/main/core/resource'

const FlashcardMenu = props =>
  <ResourceMenu
    overview={props.overview}
  />

FlashcardMenu.propTypes = {
  overview: T.bool.isRequired
}

export {
  FlashcardMenu
}
