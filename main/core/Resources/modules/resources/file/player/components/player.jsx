import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import get from 'lodash/get'

import {currentUser, hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'

import {makeId} from '#/main/core/scaffolding/id'
import {getFile} from '#/main/core/files'
import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {File as FileTypes} from '#/main/core/files/prop-types'
import {selectors} from '#/main/core/resources/file/store'
import {actions} from '#/main/core/resources/file/store'
import {selectors as nodeSelectors} from '#/main/core/resource/store/selectors'
import {Comments} from '#/main/core/layout/comment/components/comments'

// TODO : display a standard player with file info if no custom one

const authenticatedUser = currentUser()

const Player = (props) => {
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
                  comments={props.resourceNode.comments}
                  canComment={!!authenticatedUser}
                  createComment={(content) => props.createComment(content, props.resourceNode)}
                  editComment={(commentId, content) => props.editComment(commentId, content, props.resourceNode)}
                  canEditComment={(comment) => authenticatedUser && comment.user.id === authenticatedUser.id}
                  deleteComment={(commentId) => props.deleteComment(commentId)}
                  canManage={props.canEdit}
                />
              }
            </div>
          )
        }
        props.download(props.resourceNode)
      }}
    >
    </Await>
  )
}

Player.propTypes = {
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

const FilePlayer = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state),
    resourceNode: nodeSelectors.resourceNode(state),
    canEdit: hasPermission('edit', nodeSelectors.resourceNode(state))
  }),
  (dispatch) => ({
    download(resourceNode) {
      dispatch(actions.download(resourceNode))
    },
    createComment(content, resourceNode) {
      dispatch(actions.createComment({
        id: makeId(),
        content: content,
        user: authenticatedUser,
        resourceNode: resourceNode
      }))
    },
    editComment(commentId, content, resourceNode) {
      const comment = resourceNode.comments.find(comment => comment.id === commentId)

      if (comment) {
        const newComment = cloneDeep(comment)
        set(newComment, 'content', content)
        dispatch(actions.editComment(newComment))
      }
    },
    deleteComment(commentId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        icon: 'fa fa-fw fa-trash-o',
        title: trans('comment_deletion'),
        question: trans('comment_deletion_confirm_message'),
        dangerous: true,
        handleConfirm: () => dispatch(actions.deleteComment(commentId))
      }))
    }
  })
)(Player)

export {
  FilePlayer
}
