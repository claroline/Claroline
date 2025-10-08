import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {PageContent, PageSection} from '#/main/app/page'
import {Html} from '#/main/app/components/html'

import {selectors as resourceSelectors} from '#/main/core/resource'
import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {Chapter} from '#/plugin/lesson/resources/lesson/player/components/chapter'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

/**
 * An inline player used when the lesson is configured to display all the pages one after another.
 */
const PlayerModeInline = (props) => {
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const description = get(resourceNode, 'meta.descriptionHtml', null)
  const embedded = useSelector(resourceSelectors.embedded)

  const pages = useSelector(selectors.pages)
  const tree = useSelector(selectors.tree)
  const numbering = useSelector(selectors.numbering)

  return (
    <PageContent className={classes('d-flex flex-column', {
      'mx-n4': embedded
    })} poster={resourceNode.poster}>
      {description &&
        <PageSection className={classes({
          'pt-5': !embedded
        })}>
          <Html className="content-text mb-5">{description}</Html>
        </PageSection>
      }

      {pages.map(page => (
        <div key={page.id} id={'page-'+page.id} role="presentation">
          <Chapter
            path={props.path}
            level={page.level}
            title={true}
            numbering={getNumbering(numbering, tree.children, page)}
            chapter={page}
          />
        </div>
      ))}
    </PageContent>
  )
}

PlayerModeInline.propTypes = {
  path: T.string
}

export {
  PlayerModeInline
}
