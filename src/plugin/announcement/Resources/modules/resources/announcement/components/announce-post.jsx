import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {UserMicro} from '#/main/core/user/components/micro'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'

const AnnouncePost = props =>
  <div className={classes('announce-post panel panel-default', {
    'active-post': props.active
  })}>
    <div className="announce-content panel-body">
      {props.title &&
        <h2 className="announce-title">{props.title}</h2>
      }

      <div className="announce-meta">
        <div className="announce-info">
          {props.meta.author ?
            <UserMicro name={props.meta.author} /> :
            <UserMicro {...props.meta.creator} link={true} />
          }

          <div className="date">
            {props.meta.publishedAt ?
              trans('published_at', {date: displayDate(props.meta.publishedAt, true, true)}) : trans('not_published')
            }
          </div>
        </div>

        <div className="announce-actions">
          {!props.active &&
            <Button
              id={`${props.id}-show`}
              className="btn-link"
              type={LINK_BUTTON}
              icon="fa fa-fw fa-expand"
              label={trans('show')}
              tooltip="top"
              target={`${props.path}/${props.id}`}
            />
          }

          {props.editable &&
            <Button
              id={`${props.id}-send`}
              className="btn-link"
              type={LINK_BUTTON}
              icon="fa fa-fw fa-paper-plane"
              label={trans('send-announce', {}, 'actions')}
              tooltip="top"
              target={`${props.path}/${props.id}/send`}
            />
          }

          {props.editable &&
            <Button
              id={`${props.id}-edit`}
              className="btn-link"
              type={LINK_BUTTON}
              icon="fa fa-fw fa-pencil"
              label={trans('edit')}
              tooltip="top"
              target={`${props.path}/${props.id}/edit`}
            />
          }

          {props.deletable &&
            <Button
              id={`${props.id}-delete`}
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
        {props.content}
      </ContentHtml>
    </div>
  </div>

implementPropTypes(AnnouncePost, AnnouncementTypes, {
  path: T.string.isRequired,
  active: T.bool,
  deletable: T.bool.isRequired,
  editable: T.bool.isRequired,
  sendPost: T.func.isRequired,
  removePost: T.func.isRequired
}, {
  active: false
})

export {
  AnnouncePost
}
