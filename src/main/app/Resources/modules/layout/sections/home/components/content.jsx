import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageSimple, PageContent} from '#/main/app/page'
import {ToolMain} from '#/main/core/tool/containers/main'
import {ContentHtml} from '#/main/app/content/components/html'

import {constants} from '#/main/app/layout/sections/home/constants'

const HomeContent = props => {
  switch (props.type) {
    case constants.HOME_TYPE_HTML:
      return (
        <PageSimple>
          <PageContent>
            <ContentHtml>{props.content}</ContentHtml>
          </PageContent>
        </PageSimple>
      )

    case constants.HOME_TYPE_TOOL:
      return (
        <ToolMain />
      )
  }

  return null
}

HomeContent.propTypes = {
  type: T.string.isRequired,
  content: T.string
}

export {
  HomeContent
}
