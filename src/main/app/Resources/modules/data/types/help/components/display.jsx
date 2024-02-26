import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'

const HelpDisplay = (props) =>
  <ContentHtml>{props.message}</ContentHtml>

HelpDisplay.propTypes = {
  message: T.string
}

export {
  HelpDisplay
}
