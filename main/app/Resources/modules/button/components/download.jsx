import React from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Button as ButtonTypes} from '#/main/app/button/prop-types'
import {UrlButton} from '#/main/app/button/components/url'

/**
 * Download button.
 * Renders a component that will trigger a file download on click.
 *
 * @param props
 * @constructor
 */
const DownloadButton = props =>
  <UrlButton
    {...omit(props, 'file')}
    target={props.file.url}
  >
    {props.children || props.file.name || props.file.url}
  </UrlButton>

implementPropTypes(DownloadButton, ButtonTypes, {
  file: T.shape({
    name: T.string,
    mimeType: T.string,
    url: T.string.isRequired
  }).isRequired
})

export {
  DownloadButton
}
