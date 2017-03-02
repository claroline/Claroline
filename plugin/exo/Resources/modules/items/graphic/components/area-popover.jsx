import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'
import {t, tex} from './../../../utils/translate'
import {ColorPicker} from './../../../components/form/color-picker.jsx'
import {TooltipButton} from './../../../components/form/tooltip-button.jsx'
import {Textarea} from './../../../components/form/textarea.jsx'

export class AreaPopover extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <Popover
        id="area-popover"
        className={classes('area-popover', {
          'has-feedback': this.state.showFeedback
        })}
        placement="top"
        positionLeft={this.props.left}
        positionTop={this.props.top}
      >
        <div className="base-controls">
          <div>
            <label className="control-label" htmlFor="popover-color-picker">
              {tex('color')}
            </label>
            <ColorPicker
              id="popover-color-picker"
              color={this.props.color}
              onPick={color => this.props.onPickColor(color.hex)}
            />
          </div>
          <div>
            <label className="control-label" htmlFor="popover-score">
              {tex('score')}
            </label>
            <input
              type="number"
              id="popover-score"
              className="form-control score-input"
              value={this.props.score}
              onChange={e => this.props.onChangeScore(parseFloat(e.target.value))}
            />
          </div>
          <TooltipButton
            id="area-popover-feedback-tip"
            className="fa fa-comments-o"
            title={tex('graphic_feedback_info')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          />
        </div>
        {this.state.showFeedback &&
          <div className="feedback-container">
            <Textarea
              id="graphic-popover-feedback"
              title={tex('feedback')}
              content={this.props.feedback}
              onChange={this.props.onChangeFeedback}
            />
          </div>
        }
        <TooltipButton
          id="area-popover-close-tip"
          className="fa fa-close close-tip-btn"
          title={t('close')}
          onClick={this.props.onClose}
        />
      </Popover>
    )
  }
}

AreaPopover.propTypes = {
  left: T.number.isRequired,
  top: T.number.isRequired,
  color: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string.isRequired,
  onChangeScore: T.func.isRequired,
  onChangeFeedback: T.func.isRequired,
  onPickColor: T.func.isRequired,
  onClose: T.func.isRequired
}
