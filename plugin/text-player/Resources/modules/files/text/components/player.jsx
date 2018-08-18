import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {getPlainText} from '#/main/app/data/html/utils'

import {Text as TextTypes} from '#/plugin/text-player/files/text/prop-types'

const TextPlayer = props =>
  <HtmlText>
    {props.file.isHtml ?
      props.file.content :
      getPlainText(props.file.content).replace(/(?:\r\n|\r|\n)/g, '<br />')
    }
  </HtmlText>

TextPlayer.propTypes = {
  file: T.shape(
    TextTypes.propTypes
  ).isRequired
}

export {
  TextPlayer
}
