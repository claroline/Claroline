import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {copy} from '#/main/app/clipboard'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {FilePlayer} from '#/main/core/resources/file/player/components/player'
import {FileEditor} from '#/main/core/resources/file/editor/components/editor'

const FileResource = props =>
  <ResourcePage
    customActions={[
      {
        name: 'clipboard',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-clipboard',
        label: trans('copy_permalink_to_clipboard'),
        permission: 'open',
        callback: () => copy(props.url)
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: FilePlayer
        }, {
          path: '/edit',
          component: FileEditor
        }
      ]}
    />
  </ResourcePage>

FileResource.propTypes = {
  url: T.string
}

export {
  FileResource
}