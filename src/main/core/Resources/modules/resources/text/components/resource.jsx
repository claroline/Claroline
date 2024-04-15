import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'
import {Resource, ResourcePage} from '#/main/core/resource'

import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/containers/editor'

const TextResource = (props) =>
  <Resource
    {...omit(props, 'canExport', 'text')}
  >
    <ResourcePage
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
      routes={[
        {
          path: '/',
          component: Player,
          exact: true
        }, {
          path: '/edit',
          component: Editor
        }
      ]}
    />
  </Resource>

TextResource.propTypes = {
  canExport: T.bool.isRequired,
  text: T.shape(
    TextTypes.propTypes
  ).isRequired
}

export {
  TextResource
}
