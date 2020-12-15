import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'

const TextContentModal = (props) =>
  <ContentHtml className="text-content-modal">
    {props.data}
  </ContentHtml>

TextContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}

export {
  TextContentModal
}
