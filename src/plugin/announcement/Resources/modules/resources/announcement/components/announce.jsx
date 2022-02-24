import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {hasPermission} from '#/main/app/security'

import {selectors as resourceSelect} from '#/main/core/resource/store'

import {actions, selectors} from '#/plugin/announcement/resources/announcement/store'
import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {AnnouncePost} from '#/plugin/announcement/resources/announcement/components/announce-post'

const AnnounceDetail = props =>
  <Fragment>
    <div className="announces-sort">
      <Button
        className="btn btn-link"
        type={LINK_BUTTON}
        icon="fa fa-fw fa-list"
        label={trans('announcements_list', {}, 'announcement')}
        target={props.path}
        primary={true}
      />
    </div>

    <AnnouncePost
      active={true}
      aggregateId={props.aggregateId}
      announcement={props.announcement}
      workspaceRoles={props.workspaceRoles}
      removePost={() => props.removePost(props.aggregateId, props.announcement)}
      editable={props.editable}
      deletable={props.deletable}
      path={props.path}
    />
  </Fragment>

AnnounceDetail.propTypes = {
  path: T.string.isRequired,
  aggregateId: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspaceRoles: T.array,
  removePost: T.func.isRequired,
  editable: T.bool.isRequired,
  deletable: T.bool.isRequired
}

const Announce = connect(
  state => ({
    path: resourceSelect.path(state),
    aggregateId: selectors.aggregateId(state),
    announcement: selectors.detail(state),
    workspaceRoles: selectors.workspaceRoles(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state)),
    deletable: hasPermission('delete', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    removePost(aggregateId, announcePost) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: trans('remove_announce', {}, 'announcement'),
          question: trans('remove_announce_confirm', {}, 'announcement'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeAnnounce(aggregateId, announcePost))
        })
      )
    }
  })
)(AnnounceDetail)

export {
  Announce
}
