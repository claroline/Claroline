import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {TextPlayer} from '#/main/core/resources/text/components/player'
import {TextEditor} from '#/main/core/resources/text/components/editor'

const TextResource = (props) =>
  <Resource
    {...omit(props, 'canExport', 'text')}
    actions={[
      {
        name: 'export-pdf',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-pdf',
        label: trans('export-pdf', {}, 'actions'),
        displayed: props.canExport,
        target: ['apiv2_resource_text_download_pdf', {id: props.text.id}],
        group: trans('transfer')
      }
    ]}
    editor={TextEditor}
    pages={[
      {
        path: '/',
        component: TextPlayer,
        exact: true
      }
    ]}
  />

TextResource.propTypes = {
  canExport: T.bool.isRequired,
  text: T.shape(
    TextTypes.propTypes
  ).isRequired
}

export {
  TextResource
}
