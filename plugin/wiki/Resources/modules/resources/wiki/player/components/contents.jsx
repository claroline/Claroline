import React, {Component} from 'react'
import {trans} from '#/main/core/translation'
import {PropTypes as T} from 'prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

// todo : replace by stateless component

class ContentSection extends Component {
  constructor(props) {
    super(props)

    this.goToSection = this.goToSection.bind(this)
  }

  goToSection() {
    let node = document.getElementById(`wiki-section-${this.props.section.id}`)
    if (node) {
      node.scrollIntoView({block: 'end', behavior: 'smooth', inline: 'center'})
    }    
  }
  
  render() {
    return (
      <div className="wiki-contents-section">
        <div className="wiki-contents-section-title">
          {this.props.num && Array.isArray(this.props.num) &&
          <span className="ordering">{this.props.num.join('.')}</span>
          }
          <Button
            className="title"
            type={CALLBACK_BUTTON}
            callback={this.goToSection}
            label={this.props.section.activeContribution.title}
          />
        </div>
        {
          this.props.section.children &&
          this.props.section.children.map(
            (section, index) => <ContentSection
              section={section}
              key={section.id}
              num={this.props.num.concat([index + 1])}
            />
          )
        }
      </div>    
    )
  }
}

ContentSection.propTypes = {
  section: T.object.isRequired,
  num: T.arrayOf(T.number).isRequired
}

const Contents = props =>
  <div className="wiki-contents-container">
    <div className="wiki-contents">
      <h5 className="wiki-contents-title text-center">{trans('wiki_contents', {}, 'icap_wiki')}</h5>
      <div className="wiki-contents-inner">
        {
          props.sectionTree.children &&
          props.sectionTree.children.map(
            (section, index) => <ContentSection
              section={section}
              key={section.id}
              num={[index + 1]}
            />
          )
        }
      </div>
    </div>
  </div>

Contents.propTypes = {
  sectionTree: T.object.isRequired
}

export {
  Contents
}