import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {localeDate} from '#/main/core/layout/data/types/date/utils'
import {User as UserTypes} from '#/main/core/layout/user/prop-types'

import {UserMicro} from '#/main/core/layout/user/components/user-micro.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TooltipLink} from '#/main/core/layout/button/components/tooltip-link.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

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
            <UserMicro {...props.meta.creator} />
          }

          <div className="date">
            {props.meta.publishedAt ?
              t('published_at', {date: localeDate(props.meta.publishedAt)}) : t('not_published')
            }
          </div>
        </div>

        <div className="announce-actions">
          {!props.active &&
            <TooltipLink
              id={`${props.id}-show`}
              title={t('show')}
              className="btn-link-default"
              target={`#/${props.id}`}
            >
              <span className="fa fa-fw fa-expand" />
            </TooltipLink>
          }

          <TooltipButton
            id={`${props.id}-send`}
            title={t('send_mail')}
            onClick={props.sendPost}
            className="btn-link-default"
          >
            <span className="fa fa-fw fa-at" />
          </TooltipButton>

          <TooltipLink
            id={`${props.id}-edit`}
            title={t('edit')}
            target={`#/${props.id}/edit`}
            className="btn-link-default"
          >
            <span className="fa fa-fw fa-pencil" />
          </TooltipLink>

          <TooltipButton
            id={`${props.id}-delete`}
            title={t('delete')}
            onClick={props.removePost}
            className="btn-link-danger"
          >
            <span className="fa fa-fw fa-trash-o" />
          </TooltipButton>
        </div>
      </div>

      <HtmlText>
        {props.content}
      </HtmlText>
    </div>
  </div>

AnnouncePost.propTypes = {
  id: T.string.isRequired,
  active: T.bool,
  title: T.string,
  content: T.string.isRequired,
  meta: T.shape({
    created: T.string.isRequired,
    creator: T.shape(UserTypes.propTypes).isRequired,
    author: T.string,
    publishedAt: T.string
  }).isRequired,
  restrictions: T.shape({
    visible: T.bool.isRequired
  }).isRequired,
  sendPost: T.func.isRequired,
  removePost: T.func.isRequired
}

AnnouncePost.defaultProps = {
  active: false
}

export {
  AnnouncePost
}
