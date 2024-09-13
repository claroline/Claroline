import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {getPlainText} from '#/main/app/data/types/html/utils'
import {trans} from '#/main/app/intl'
import {Datetime} from '#/main/app/components/date'
import {LinkButton} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {PageSection} from '#/main/app/page'
import {UserMicro} from '#/main/core/user/components/micro'
import {ResourceOverview} from '#/main/core/resource'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const Announce = (props) =>
  <article className={classes('d-flex flex-column', props.className)}>
    {props.announcement.poster &&
      <Thumbnail
        className="rounded-4 mb-4"
        size="lg"
        thumbnail={props.announcement.poster}
        name={props.announcement.title}
      />
    }

    <div className="text-body-tertiary mt-auto d-flex align-items-center gap-3" role="presentation">
      {get(props.announcement, 'meta.publishedAt') &&
        <Datetime className="fs-sm" value={get(props.announcement, 'meta.publishedAt')} long={true} />
      }

      {!isEmpty(props.announcement.tags) &&
        <div className="" role="presentation">
          {props.announcement.tags.map(tag =>
            <span key={tag} className="badge text-secondary-emphasis bg-secondary-subtle fs-sm lh-base">{tag}</span>
          )}
        </div>
      }
    </div>

    <LinkButton target={`${props.path}/${props.announcement.id}`} className="text-reset text-decoration-none d-flex flex-column">
      <h1 className="h5 mt-3 mb-0">{props.announcement.title}</h1>

      {props.announcement.content &&
        <p className="text-body-secondary mb-0 mt-4 announce-content">
          {getPlainText(props.announcement.content)}
        </p>
      }
    </LinkButton>

    <UserMicro
      className="fs-sm mt-4 fw-bolder"
      {...get(props.announcement, 'meta.creator', {})}
      noStatus={true}
      link={true}
    />
  </article>

Announce.propTypes = {
  className: T.string,
  path: T.string.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired
}

const AnnouncementOverview = (props) =>
  <ResourceOverview>
    <PageSection size="lg" className="px-4 mb-5">
      {0 === props.posts.length &&
        <ContentPlaceholder
          size="lg"
          icon="fa fa-bullhorn"
          title={trans('no_announcement', {}, 'announcement')}
        />
      }

      {0 !== props.posts.length &&
        <div className="announce-posts announce-posts-grid" role="presentation">
          {props.posts.map(post =>
            <Announce
              className="announce-post"
              key={post.id}
              path={props.path}
              announcement={post}
            />
          )}
        </div>
      }
    </PageSection>
  </ResourceOverview>

AnnouncementOverview.propTypes = {
  path: T.string.isRequired,
  posts: T.arrayOf(
    T.shape(AnnouncementTypes.propTypes)
  ).isRequired
}

export {
  AnnouncementOverview
}
