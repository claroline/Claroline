import  React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Badge} from '#/main/app/components/badge'
import {trans} from '#/main/app/intl'

const TagsSkeleton = ({
  className
}) => {
  return (
    <div className={classes('d-flex flex-row gap-1', className)} role="presentation">
      <span className="badge fs-sm lh-base placeholder text-body">{trans('tags')}</span>
      <span className="badge fs-sm lh-base placeholder text-body">{trans('tags')}</span>
      <span className="badge fs-sm lh-base placeholder text-body">{trans('tags')}</span>
    </div>
  )
}

const Tags = ({className, tags = []}) =>
  <div className={classes('d-flex flex-row gap-1', className)} role="presentation">
    {tags.map(tag =>
      <Badge key={tag} variant="secondary" subtle={true} className="fs-sm lh-base">{tag}</Badge>
    )}
  </div>

Tags.propTypes = {
  className: T.string,
  tags: T.arrayOf(T.string).isRequired
}

export {
  Tags,
  TagsSkeleton
}
