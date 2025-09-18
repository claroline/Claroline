import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {selectors} from '#/main/core/tool/store'
import {ToolPage} from '#/main/core/tool/components/page'
import {PageContent} from '#/main/app/page'
import {trans} from '#/main/app/intl'

const ToolOverview = props => {
  const tool = useSelector(selectors.tool)

  return (
    <ToolPage title={trans(tool.name, {}, 'tools')}>
      <PageContent
        poster={get(tool, 'poster')}
        title={trans(tool.name, {}, 'tools')}
      >
        {props.children}
      </PageContent>
    </ToolPage>
  )
}

ToolOverview.propTypes = {
  children: T.node
}

export {
  ToolOverview
}
