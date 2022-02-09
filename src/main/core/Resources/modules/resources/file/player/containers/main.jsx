import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {PlayerMain as PlayerMainComponent} from '#/main/core/resources/file/player/components/main'
import {actions, selectors} from '#/main/core/resources/file/store'

const PlayerMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    mimeType: selectors.mimeType(state),
    file: selectors.file(state),
    resourceNode: resourceSelectors.resourceNode(state),
    workspace: resourceSelectors.workspace(state),
    canEdit: hasPermission('edit', resourceSelectors.resourceNode(state))
  }),
  (dispatch) => ({
    download(resourceNode) {
      dispatch(actions.download(resourceNode))
    },
    createComment(content, resourceNode, user) {
      dispatch(actions.createComment({
        content: content,
        user: user,
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
)(PlayerMainComponent)

export {
  PlayerMain
}
