import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'

import {PageContent} from '#/main/app/page'
import {selectors as resourceSelectors} from '#/main/core/resource'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonPlayerNav} from '#/plugin/lesson/resources/lesson/player/components/nav'
import {Chapter} from '#/plugin/lesson/resources/lesson/player/components/chapter'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

/**
 * Lesson player when "page" (One page and all its children per page) pagination is enabled.
 */
const PlayerModePageInline = (props) => {
  const embedded = useSelector(resourceSelectors.embedded)

  const showNavigation = useSelector(selectors.showNavigation)
  const lessonNumbering = useSelector(selectors.numbering)
  const tree = useSelector(selectors.tree)
  const pages = useSelector(selectors.pages)

  const currentPage = useSelector(selectors.currentPage)
  const pageIndex = useSelector(selectors.currentPageIndex)

  let firstPageIndex
  if (1 === currentPage.level) {
    firstPageIndex = pageIndex
  } else {
    // find the root parent of the page
    // it's the first level 1 step when going backward in the tree
    firstPageIndex = (pages.length - 1 - pages.slice().reverse().findIndex((s, i) => i > (pages.length - 1 - pageIndex) && 1 === s.level))
  }

  let nextPageIndex
  if (firstPageIndex + 1 < pages.length) {
    nextPageIndex = pages.findIndex((s, i) => i > pageIndex && 1 === s.level)
  }

  let previous
  if (0 !== firstPageIndex) {
    previous = pages.slice().reverse().find((s, i) => i > (pages.length - 1 - firstPageIndex) && 1 === s.level)
  }
  let next
  if (-1 !== nextPageIndex) {
    next = pages[nextPageIndex]
  }

  return (
    <PageContent className={classes('d-flex flex-column w-100', {
      'mx-n4': embedded
    })} poster={currentPage.poster}>
      <div role="presentation" className="h-100 flex-shrink-0 d-flex flex-column">
        {pages
          .slice(firstPageIndex, -1 !== nextPageIndex ? nextPageIndex : undefined)
          .map((page, pageIndex) => (
            <div key={page.id} id={'page-'+page.id} role="presentation">
              <Chapter
                path={props.path}
                level={page.level}
                title={true}
                poster={0 !== pageIndex ? page.poster : undefined}
                chapter={page}
                numbering={getNumbering(lessonNumbering, tree.children, page)}
              />
            </div>
          ))
        }

        {showNavigation &&
          <LessonPlayerNav
            path={props.path}
            previousPage={previous}
            nextPage={next}
          />
        }
      </div>
    </PageContent>
  )
}

PlayerModePageInline.propTypes = {
  path: T.string.isRequired
}

export {
  PlayerModePageInline
}
