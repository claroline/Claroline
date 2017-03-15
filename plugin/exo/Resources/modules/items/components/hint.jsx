import React, {Component, PropTypes as T}  from 'react'

import {tex, transChoice} from './../../utils/translate'

class Hint extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showContent: false
    }
  }

  render() {
    return (
      <article className="hint">
        <header>
          <span className="fa fa-fw fa-lightbulb-o" />
          {tex('hint')}&nbsp;{this.props.number}

          {this.props.penalty > 0 &&
            <span className="hint-penalty">
              {transChoice('hint_penalty', this.props.penalty, {count: this.props.penalty}, 'ujm_exo')}
            </span>
          }
        </header>
        {this.state.showContent && this.props.content &&
          <div className="hint-content" dangerouslySetInnerHTML={{__html: this.props.content}}></div>
        }
      </article>
    )
  }
}

Hint.propTypes = {
  number: T.number.isRequired,
  penalty: T.number,
  content: T.string,
  showHint: T.func.isRequired
}

Hint.defaultProps = {

}

export {Hint}
