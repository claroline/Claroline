import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {Helmet} from 'react-helmet'

import {url} from '#/main/app/api'
import {theme} from '#/main/theme/config'
import {Await} from '#/main/app/components/await'

import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {getFile} from '#/main/core/files'
import {File as FileTypes} from '#/main/core/files/prop-types'

import {constants} from '#/main/core/resources/file/constants'

import {PlayerOverview} from '#/main/core/resources/file/player/components/overview'

const PlayerMain = (props) => {
  if (!props.embedded && constants.OPENING_DOWNLOAD === props.file.opening) {
    props.download(props.resourceNode)
  } else if (constants.OPENING_BROWSER === props.file.opening) {
    window.location.replace(url(['claro_resource_file_raw', {file: props.file.id}]))
  }

  return (
    <Await
      for={getFile(props.mimeType)}
      then={module => {
        if (get(module, 'fileType.components.player')) {
          return (
            <>
              {createElement(get(module, 'fileType.components.player'), {
                file: props.file,
                path: props.path
              })}

              {get(module, 'fileType.styles') &&
                <Helmet>
                  {get(module, 'fileType.styles').map(style =>
                    <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
                  )}
                </Helmet>
              }
            </>
          )
        }

        if (!props.embedded) {
          props.download(props.resourceNode)
        }

        return (
          <PlayerOverview
            file={props.file}
            resourceNode={props.resourceNode}
            workspace={props.workspace}
            download={props.download}
          />
        )
      }}
    />
  )
}

PlayerMain.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  mimeType: T.string.isRequired,
  download: T.func.isRequired,
  resourceNode: T.shape(ResourceNodeType.propTypes).isRequired,
  embedded: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired,
  workspace: T.object,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  deleteComment: T.func.isRequired
}

export {
  PlayerMain
}
