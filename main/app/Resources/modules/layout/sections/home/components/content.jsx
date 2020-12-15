import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageSimple} from '#/main/app/page/components/simple'
import {ToolMain} from '#/main/core/tool/containers/main'

import {constants} from '#/main/app/layout/sections/home/constants'

const HomeContent = props => {
  switch (props.type) {
    case constants.HOME_TYPE_HTML:
      return (
        <PageSimple>
          {props.content}
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
