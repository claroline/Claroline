import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors} from '#/main/core/tools/progression/store'
import {ProgressionItem as ProgressionItemType} from '#/main/core/tools/progression/prop-types'
import {ToolPage} from '#/main/core/tool/containers/page'
import {List} from '#/main/core/tools/progression/components/list'

const ProgressionToolComponent = (props) =>
  <ToolPage>
    <List
      items={props.items}
      levelMax={props.levelMax}
    />
  </ToolPage>

ProgressionToolComponent.propTypes = {
  items: T.arrayOf(T.shape(ProgressionItemType.propTypes)),
  levelMax: T.number.isRequired
}

const ProgressionTool = connect(
  state => ({
    items: selectors.items(state),
    levelMax: selectors.levelMax(state)
  })
)(ProgressionToolComponent)

export {
  ProgressionTool
}
