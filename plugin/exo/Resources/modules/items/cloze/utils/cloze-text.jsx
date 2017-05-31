import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import ReactDOM from 'react-dom'
import classes from 'classnames'

export class ClozeText extends Component {
  componentDidMount() {
    this.renderHoles()
  }

  componentDidUpdate(prevProps) {
    if (prevProps !== this.props) {
      this.renderHoles()
    }
  }

  componentWillUnmount() {
    this.props.holes.map(hole => {
      const holeContainer = document.getElementById(`${this.props.anchorPrefix}-${hole.id}`)
      if (holeContainer) {
        ReactDOM.unmountComponentAtNode(holeContainer)
      }
    })
  }

  renderHoles() {
    this.props.holes.map(hole => {
      ReactDOM.render(
        hole.component,
        document.getElementById(`${this.props.anchorPrefix}-${hole.id}`)
      )
    })
  }

  render() {
    let parsedHtml = this.props.text

    // Replaces placeholders [[hole_id]] by HTML tags to bind Hole component inside
    this.props.holes.map(hole => {
      let reg = new RegExp(`(\\[\\[${hole.id}\\]\\])`, 'g')
      parsedHtml = parsedHtml.replace(reg, `<span id="${this.props.anchorPrefix}-${hole.id}"></span>`)
    })

    return (
      <div className={classes('cloze-text', this.props.className)} dangerouslySetInnerHTML={{__html: parsedHtml}} />
    )
  }
}

ClozeText.propTypes = {
  anchorPrefix: T.string.isRequired,
  className: T.string,
  text: T.string.isRequired,
  holes: T.arrayOf(T.shape({
    id: T.string.isRequired,
    component: T.node.isRequired
  }))
}
