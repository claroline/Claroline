import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'

import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {getFile} from '#/main/core/files'
import {File as FileTypes} from '#/main/core/files/prop-types'

import {Comments} from '#/main/core/layout/comment/components/comments'

// TODO : display a standard player with file info if no custom one
const PlayerMain = (props) => {
  // FIXME : ugly
  if (props.file.autoDownload) {
    props.download(props.resourceNode)
  }

  return (
    <Await
      for={getFile(props.mimeType)}
      then={module => {
        if (get(module, 'fileType.components.player')) {
          return (
            <div>
              <div>
                {React.createElement(get(module, 'fileType.components.player'), {
                  file: props.file
                })}

                {get(module, 'fileType.styles') &&
                  <link rel="stylesheet" type="text/css" href={theme(get(module, 'fileType.styles'))} />
                }
              </div>

              {props.file && props.file.commentsActivated &&
                <Comments
                  currentUser={props.currentUser}
                  comments={props.resourceNode.comments}
                  canComment={!!props.currentUser}
                  createComment={(content) => props.createComment(content, props.resourceNode, props.currentUser)}
                  editComment={(commentId, content) => props.editComment(commentId, content, props.resourceNode)}
                  canEditComment={(comment) => props.currentUser && comment.user.id === props.currentUser.id}
                  deleteComment={(commentId) => props.deleteComment(commentId)}
                  canManage={props.canEdit}
                />
              }
            </div>
          )
        }
        props.download(props.resourceNode)
      }}
    />
  )
}

PlayerMain.propTypes = {
  currentUser: T.object,
  mimeType: T.string.isRequired,
  download: T.func.isRequired,
  resourceNode: T.shape(ResourceNodeType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  deleteComment: T.func.isRequired
}

export {
  PlayerMain
}
