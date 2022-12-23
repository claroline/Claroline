import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {copy} from '#/main/app/clipboard'

import {File as FileType} from '#/main/core/files/prop-types'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {PlayerMain} from '#/main/core/resources/file/player/containers/main'
import {EditorMain} from '#/main/core/resources/file/editor/containers/main'

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
    routes={[
      {
        path: '/',
        exact: true,
        component: PlayerMain
      }, {
        path: '/edit',
        component: EditorMain,
        onEnter: () => props.resetForm(props.file)
      }
    ]}
  />

FileResource.propTypes = {
  path: T.string.isRequired,
  file: T.shape(FileType.propTypes),
  url: T.string,
  resetForm: T.func.isRequired
}

export {
  FileResource
}
