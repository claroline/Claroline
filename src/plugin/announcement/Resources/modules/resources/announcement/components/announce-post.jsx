import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {UserMicro} from '#/main/core/user/components/micro'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {MODAL_ANNOUNCEMENT_SENDING} from '#/plugin/announcement/resources/announcement/modals/sending'

const AnnouncePost = props =>
  <div className={classes('announce-post panel panel-default', {
    'active-post': props.active
  })}>
    <div className="announce-content panel-body">
      {props.announcement.title &&
        <h2 className="announce-title">{props.announcement.title}</h2>
      }

      <div className="announce-meta">
        <div className="announce-info">
          {props.announcement.meta.author ?
            <UserMicro name={props.announcement.meta.author} /> :
            <UserMicro {...props.announcement.meta.creator} link={true} />
          }

          <div className="date">
            {props.announcement.meta.publishedAt ?
              trans('published_at', {date: displayDate(props.announcement.meta.publishedAt, true, true)}) : trans('not_published')
            }
          </div>
        </div>

        <div className="announce-actions">
          {!props.active &&
            <Button
              id={`${props.announcement.id}-show`}
              className="btn-link"
              type={LINK_BUTTON}
              icon="fa fa-fw fa-expand"
              label={trans('show')}
              tooltip="top"
              target={`${props.path}/${props.announcement.id}`}
            />
          }

          {props.editable &&
            <Button
              id={`${props.announcement.id}-send`}
              className="btn-link"
              type={MODAL_BUTTON}
              icon="fa fa-fw fa-paper-plane"
              label={trans('send-announce', {}, 'actions')}
              tooltip="top"
              target={`${props.path}/${props.announcement.id}/send`}
              modal={[MODAL_ANNOUNCEMENT_SENDING, {
                aggregateId: props.aggregateId,
                announcement: props.announcement,
                workspaceRoles: props.workspaceRoles
              }]}
            />
          }

          {props.editable &&
            <Button
              id={`${props.announcement.id}-edit`}
              className="btn-link"
              type={LINK_BUTTON}
              icon="fa fa-fw fa-pencil"
              label={trans('edit')}
              tooltip="top"
              target={`${props.path}/${props.announcement.id}/edit`}
            />
          }

          {props.deletable &&
            <Button
              id={`${props.announcement.id}-delete`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash-o"
              label={trans('delete')}
              tooltip="top"
              callback={props.removePost}
              dangerous={true}
            />
          }
        </div>
      </div>

      <ContentHtml>
        {props.announcement.content}
      </ContentHtml>
    </div>
  </div>

AnnouncePost.propTypes = {
  path: T.string.isRequired,
  aggregateId: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspaceRoles: T.array,
  active: T.bool,
  deletable: T.bool.isRequired,
  editable: T.bool.isRequired,
  removePost: T.func.isRequired
}

AnnouncePost.defaultProps = {
  active: false
}

export {
  AnnouncePost
}
