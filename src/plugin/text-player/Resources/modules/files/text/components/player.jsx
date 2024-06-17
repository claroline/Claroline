import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {getPlainText} from '#/main/app/data/types/html/utils'

import {Text as TextTypes} from '#/plugin/text-player/files/text/prop-types'
import {ResourcePage} from '#/main/core/resource'

const TextPlayer = props =>
  <ResourcePage>
    <ContentHtml>
      {props.file.isHtml ?
        props.file.content :
        getPlainText(props.file.content).replace(/(?:\r\n|\r|\n)/g, '<br />')
      }
    </ContentHtml>
  </ResourcePage>

TextPlayer.propTypes = {
  file: T.shape(
    TextTypes.propTypes
  ).isRequired
}

export {
  TextPlayer
}
