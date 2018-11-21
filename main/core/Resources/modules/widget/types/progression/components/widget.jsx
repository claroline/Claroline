import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {ProgressionItem as ProgressionItemType} from '#/main/core/tools/progression/prop-types'
import {List as ProgressionList} from '#/main/core/tools/progression/components/list'

class ProgressionWidget extends Component {
  componentDidMount() {
    if ('workspace' === this.props.context.type) {
      this.props.loadItems(this.props.context.data.uuid, this.props.levelMax)
    }
  }

  render() {
    return (
      <ProgressionList
        items={this.props.items}
        levelMax={this.props.levelMax}
      />
    )
  }
}

ProgressionWidget.propTypes = {
  context: T.object.isRequired,
  items: T.arrayOf(T.shape(ProgressionItemType.propTypes)),
  levelMax: T.number,
  loadItems: T.func.isRequired
}

ProgressionWidget.defaultProps = {
  items: [],
  levelMax: 1
}

export {
  ProgressionWidget
}
