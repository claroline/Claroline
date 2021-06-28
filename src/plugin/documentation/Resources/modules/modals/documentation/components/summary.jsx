import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentSummary} from '#/main/app/content/components/summary'

const DocumentationSummary = (props) => {
  function getChapterSummary(chapter) {
    return {
      type: LINK_BUTTON,
      label: chapter.title,
      target: `${props.path}/${chapter.slug}`,
      children: chapter.children ? chapter.children.map(getChapterSummary) : [],
      onClick: () => scrollTo('.modal-content')
    }
  }

  return (
    <Fragment>
      <ContentTitle
        className="chapter-title"
        level={1}
        displayLevel={2}
        title={trans('summary')}
      />

      <ContentSummary
        className="component-container"
        links={get(props.tree, 'children', []).map(getChapterSummary)}
      />
    </Fragment>
  )
}

DocumentationSummary.propTypes = {
  path: T.string.isRequired,
  tree: T.object
}

export {
  DocumentationSummary
}
