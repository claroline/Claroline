import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Collapse from 'react-bootstrap/lib/Collapse'

/**
 * Renders a toggleable fieldset.
 */
class ToggleableSet extends Component {
  constructor(props) {
    super(props)

    this.state = {
      hidden: true
    }

    this.toggle = this.toggle.bind(this)
  }

  toggle() {
    this.setState({hidden: !this.state.hidden})
  }

  render() {
    return (
      <div className="toggleable-set">
        <Collapse in={!this.state.hidden}>
          <div className="toggleable-set-group">
            {this.props.children}
          </div>
        </Collapse>

        <a
          role="button"
          className="toggleable-set-toggle"
          onClick={this.toggle}
        >
          <span className={classes('fa fa-fw', {
            'fa-caret-right': this.state.hidden,
            'fa-caret-up': !this.state.hidden
          })} />

          {this.state.hidden ? this.props.showText : this.props.hideText}
        </a>
      </div>
    )
  }
}

ToggleableSet.propTypes = {
  /**
   * Toggle button text when the section is hidden.
   */
  showText: T.string.isRequired,

  /**
   * Toggle button text when the section is shown.
   */
  hideText: T.string.isRequired,

  /**
   * Sub-section content.
   */
  children: T.any.isRequired
}

export {
  ToggleableSet
}
