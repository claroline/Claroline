import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {ProgressionItem as ProgressionItemType} from '#/plugin/analytics/tools/dashboard/prop-types'
import {ProgressionList} from '#/plugin/analytics/tools/dashboard/components/progression-list'

class ProgressionWidget extends Component {
  componentDidMount() {
    if ('workspace' === this.props.currentContext.type) {
      this.props.loadItems(this.props.currentContext.data.uuid, this.props.levelMax)
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
  currentContext: T.object.isRequired,
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
