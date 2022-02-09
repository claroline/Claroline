import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'

import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {getFile} from '#/main/core/files'
import {File as FileTypes} from '#/main/core/files/prop-types'

import {constants} from '#/main/core/resources/file/constants'
import {ContentComments} from '#/main/app/content/components/comments'
import {PlayerOverview} from '#/main/core/resources/file/player/components/overview'

// TODO : display a standard player with file info if no custom one
const PlayerMain = (props) => {
  // FIXME : ugly
  if (constants.OPENING_DOWNLOAD === props.file.opening) {
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
            <Fragment>
              {React.createElement(get(module, 'fileType.components.player'), {
                file: props.file,
                path: props.path
              })}

              {get(module, 'fileType.styles') &&
                <link rel="stylesheet" type="text/css" href={theme(get(module, 'fileType.styles'))} />
              }

              {props.file && props.file.commentsActivated &&
                <ContentComments
                  currentUser={props.currentUser}
                  comments={props.resourceNode.comments}
                  canComment={!!props.currentUser}
                  createComment={(comment) => props.createComment(comment, props.resourceNode)}
                  editComment={(comment) => props.editComment(comment, props.resourceNode)}
                  canEditComment={(comment) => props.currentUser && comment.user.id === props.currentUser.id}
                  deleteComment={(comment) => props.deleteComment(comment.id)}
                  canManage={props.canEdit}
                />
              }
            </Fragment>
          )
        }

        props.download(props.resourceNode)

        console.log(props.mimeType)

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
