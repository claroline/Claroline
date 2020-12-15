import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import ReactDOM from 'react-dom'
import classes from 'classnames'

export class SelectionText extends Component {
  componentDidMount() {
    this.renderSelections()
  }

  componentDidUpdate(prevProps) {
    if (prevProps !== this.props) {
      this.renderSelections()
    }
  }

  componentWillUnmount() {
    this.props.selections.map(selection =>
      ReactDOM.unmountComponentAtNode(document.getElementById(`${this.props.anchorPrefix}-${selection.id}`))
    )
  }

  renderSelections() {
    this.props.selections.map(selection => {
      ReactDOM.render(
        selection.component,
        document.getElementById(`${this.props.anchorPrefix}-${selection.id}`)
      )
    })
  }

  render() {
    let parsedHtml = this.props.text
    let idx = 0

    this.props.selections.forEach(selection => {
      let end = parsedHtml.slice(selection.end + idx)
      parsedHtml = parsedHtml.slice(0, selection.begin + idx)
      parsedHtml += `<span id="${this.props.anchorPrefix}-${selection.id}"></span>`
      parsedHtml += end

      idx += `<span id="${this.props.anchorPrefix}-${selection.id}"></span>`.length - (selection.end - selection.begin)
    })

    return (
      <div className={classes(this.props.className)} dangerouslySetInnerHTML={{__html: parsedHtml}} />
    )
  }
}

SelectionText.propTypes = {
  anchorPrefix: T.string.isRequired,
  className: T.string,
  text: T.string.isRequired,
  selections: T.arrayOf(T.shape({
    id: T.string.isRequired,
    component: T.node.isRequired,
    begin: T.number.isRequired,
    end: T.number.isRequired
  }))
}
