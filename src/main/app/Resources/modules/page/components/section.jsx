import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentSizing} from '#/main/app/content/components/sizing'
import {Heading} from '#/main/app/components/heading'

const PageSection = ({
  className,
  title,
  description,
  children,
  size = 'lg',
  level = 2,
  showTitle = true,
  flush = false
}) => {
  return (
    <div className={classes('page-section', className)} role="presentation">
      <ContentSizing className={classes('flex-fill d-flex flex-column', {
        'px-4': !flush
      })} size={size}>
        {title &&
          <div className={classes('page-section-title mb-3', {
            'visually-hidden': !showTitle
          })} role="presentation">
            <Heading className="h6 mb-0" level={level}>{title}</Heading>
            {description &&
              <p className="text-body-secondary mt-2 mb-0">{description}</p>
            }
          </div>
        }

        {children}
      </ContentSizing>
    </div>
  )
}

PageSection.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'xl', 'full']),
  level: T.number,
  title: T.string,
  showTitle: T.bool,
  description: T.string,
  children: T.node,
  flush: T.bool
}

export {
  PageSection
}
