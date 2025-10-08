import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {Chapter} from '#/plugin/lesson/resources/lesson/prop-types'
import {getNumbering} from '#/plugin/lesson/resources/lesson/utils'

const LessonPlayerNavSkeleton = () => {
  return (
    <nav className="d-flex flex-row content-lg px-2 mt-auto pb-5">
      <span
        className="btn btn-text-body focus-ring w-50 text-start d-flex flex-row align-items-center gap-4 justify-content-start"
      >
        <div className="d-flex flex-column overflow-hidden flex-fill align-items-start gap-1" role="presentation">
          <b className="placeholder rounded-1">{trans('previous')}</b>
          <span className="text-truncate fs-sm placeholder rounded-1 w-75" role="presentation" />
        </div>
      </span>

      <span
        className="btn btn-text-body focus-ring w-50 text-end d-flex flex-row align-items-center gap-4 justify-content-end ms-auto"
      >
        <div className="d-flex flex-column overflow-hidden flex-fill align-items-end gap-1" role="presentation">
          <b className="placeholder rounded-1">{trans('next')}</b>
          <span className="text-truncate fs-sm placeholder rounded-1 w-75" role="presentation" />
        </div>
      </span>
    </nav>
  )
}

const LessonPlayerNav = ({path, previousPage = null, nextPage = null}) => {
  const showOverview = useSelector(selectors.showOverview)
  const tree = useSelector(selectors.tree)
  const lessonNumbering = useSelector(selectors.numbering)

  let previousNumbering
  if (previousPage) {
    previousNumbering = getNumbering(lessonNumbering, tree.children, previousPage)
  }

  let nextNumbering
  if (nextPage) {
    previousNumbering = getNumbering(lessonNumbering, tree.children, nextPage)
  }

  if (showOverview || previousPage || nextPage) {
    return (
      <nav className="d-flex flex-row content-lg px-2 mt-auto pb-5">
        {previousPage &&
          <LinkButton
            className="btn btn-text-body focus-ring w-50 text-start d-flex flex-row align-items-center gap-4 justify-content-start"
            target={path+'/'+previousPage.slug}
            exact={true}
          >
            <span className="fa fa-chevron-left fs-lg" aria-hidden={true} />

            <div className="d-flex flex-column overflow-hidden flex-fill align-items-start" role="presentation">
              <b>{trans('previous')}</b>
              <span className="text-truncate fs-sm w-100" role="presentation">
                {previousNumbering ?
                  previousNumbering + ' ' + previousPage.title :
                  previousPage.title
                }
              </span>
            </div>
          </LinkButton>
        }

        {(!previousPage && showOverview) &&
          <LinkButton
            className="btn btn-text-body focus-ring w-50 text-start d-flex flex-row align-items-center gap-4 justify-content-start"
            target={path}
            exact={true}
          >
            <span className="fa fa-chevron-left fs-lg" aria-hidden={true} />

            <div className="d-flex flex-column overflow-hidden flex-fill align-items-start" role="presentation">
              <b>{trans('previous')}</b>
              <span className="text-truncate fs-sm w-100" role="presentation">
                {trans('resource_overview', {}, 'resource')}
              </span>
            </div>
          </LinkButton>
        }

        {nextPage &&
          <LinkButton
            className="btn btn-text-body focus-ring w-50 text-end d-flex flex-row align-items-center gap-4 justify-content-end ms-auto"
            target={path+'/'+nextPage.slug}
          >
            <div className="d-flex flex-column overflow-hidden flex-fill align-items-end" role="presentation">
              <b>{trans('next')}</b>
              <span className="text-truncate fs-sm w-100" role="presentation">
                {nextNumbering ?
                  nextNumbering + ' ' + nextPage.title :
                  nextPage.title
                }
              </span>
            </div>

            <span className="fa fa-chevron-right fs-lg" aria-hidden={true} />
          </LinkButton>
        }
      </nav>
    )
  }

  return null
}

LessonPlayerNav.propTypes = {
  path: T.string.isRequired,
  nextPage: T.shape(Chapter.propTypes),
  previousPage: T.shape(Chapter.propTypes)
}

export {
  LessonPlayerNav,
  LessonPlayerNavSkeleton
}
