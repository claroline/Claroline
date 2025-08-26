import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {Html} from '#/main/app/components/html'
import {Tags, TagsSkeleton} from '#/main/app/components/tags'
import {ContentPublicationSkeleton} from '#/main/app/content/components/publication'
import {TextSkeleton} from '#/main/app/components/placeholder'

const ContentSkeleton = ({meta, tags, length = 3}) =>
  <>
    {meta &&
      <div className="mb-4" role="presentation">
        <ContentPublicationSkeleton />
      </div>
    }

    {[4, 5, 3].slice(0, length).map((i) =>
      <TextSkeleton key={i} className="content-text" rows={i} />
    )}

    {tags &&
      <TagsSkeleton />
    }
  </>

ContentSkeleton.propTypes = {
  meta: T.bool,
  tags: T.bool,
  length: T.number
}

const Content = (props) =>
  <>
    {props.meta &&
      <div className={classes('text-body-tertiary d-flex align-items-center gap-3', {
        'mb-4': !!props.children || !!props.placeholder
      })} role="presentation">
        {props.meta}
      </div>
    }

    {props.children &&
      <Html className="content-text">{props.children}</Html>
    }

    {(!props.children && props.placeholder) &&
      <em className="text-body-tertiary">{props.placeholder}</em>
    }

    {!isEmpty(props.tags) &&
      <Tags tags={props.tags} className="mt-4" />
    }
  </>

Content.propTypes = {
  meta: T.node,
  children: T.string,
  placeholder: T.string,
  tags: T.arrayOf(T.string)
}

Content.defaultProps = {
  tags: []
}

export {
  Content,
  ContentSkeleton
}
