import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'

import {Contents} from '#/plugin/wiki/resources/wiki/player/components/contents'
import {WikiSection} from '#/plugin/wiki/resources/wiki/player/components/wiki-section'
import {WikiSectionTree} from '#/plugin/wiki/resources/wiki/player/components/wiki-section-tree'
import {actions} from '#/plugin/wiki/resources/wiki/player/store'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.reload()
  }

  componentDidUpdate(prevProps) {
    if (this.props.sections.invalidated && this.props.sections.invalidated !== prevProps.sections.invalidated) {
      this.reload()
    }
  }

  reload() {
    if (this.props.sections.invalidated) {
      this.props.fetchSectionTree(this.props.wiki.id)
    }
  }

  render() {
    return (
      <div className={'wiki-overview'}>
        <WikiSection
          section={this.props.sections.tree}
          displaySectionNumbers={false}
          setSectionVisibility={null}
          num={[]}
        />
        {this.props.wiki.display.contents && this.props.sections.tree.children.length > 0 &&
        <Contents sectionTree={this.props.sections.tree}/>
        }
        {this.props.sections.tree.children.length === 0 &&
        <div className="wiki-empty-message text-info">
          {trans('empty_wiki_message', {}, 'icap_wiki')}
        </div>
        }
        {this.props.sections.tree.children.length > 0 &&
        <WikiSectionTree
          sections={this.props.sections}
        />
        }
      </div>
    )
  }
}

PlayerComponent.propTypes = {
  'sections': T.object.isRequired,
  'wiki': T.object.isRequired,
  'fetchSectionTree': T.func.isRequired
}

const Player = connect(
  state => ({
    sections: selectors.sections(state),
    wiki: selectors.wiki(state)
  }),
  dispatch => ({
    fetchSectionTree: wikiId => dispatch(actions.fetchSectionTree(wikiId))
  })
)(PlayerComponent)

export {
  Player
}
