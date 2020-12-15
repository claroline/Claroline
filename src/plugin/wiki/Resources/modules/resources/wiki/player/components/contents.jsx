import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CallbackButton, CALLBACK_BUTTON} from '#/main/app/buttons/callback'

const ContentSection = props =>
  <li className="wiki-contents-item">
    <CallbackButton
      className="btn-link"
      callback={() => {
        let node = document.getElementById(`wiki-section-${props.section.id}`)
        if (node) {
          node.scrollIntoView({block: 'end', behavior: 'smooth', inline: 'center'})
        }
      }}
    >
      {props.showNumbering &&
        <span className="numbering">{props.num.join('.')}</span>
      }

      {props.section.activeContribution.title}
    </CallbackButton>

    {props.section.children && 0 < props.section.children.length &&
      <ul className="wiki-contents-items">
        {props.section.children.map((section, index) =>
          <ContentSection
            key={section.id}
            num={props.num.concat([index + 1])}
            section={section}
            showNumbering={props.showNumbering}
          />
        )}
      </ul>
    }
  </li>

ContentSection.propTypes = {
  num: T.arrayOf(T.number).isRequired,
  section: T.object.isRequired,
  showNumbering: T.bool.isRequired
}

class Contents extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: true
    }
  }

  render() {
    return (
      <div className="wiki-contents">
        <h2 className="wiki-contents-title">
          {trans('wiki_contents', {}, 'icap_wiki')}

          <Button
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon={classes('fa fa-fw', {
              'fa-chevron-up': this.state.opened,
              'fa-chevron-down': !this.state.opened
            })}
            label={trans(this.state.opened ? 'close-summary':'open-summary', {}, 'actions')}
            tooltip="left"
            callback={() => this.setState({opened: !this.state.opened})}
          />
        </h2>

        {this.state.opened && this.props.sections.children && 0 < this.props.sections.children.length &&
          <ul className="wiki-contents-items">
            {this.props.sections.children.map((section, index) =>
              <ContentSection
                key={section.id}
                num={[index + 1]}
                section={section}
                showNumbering={this.props.showNumbering}
              />
            )}
          </ul>
        }
      </div>
    )
  }
}

Contents.propTypes = {
  showNumbering: T.bool,
  sections: T.object.isRequired
}

Contents.defaultProps = {
  showNumbering: false
}

export {
  Contents
}