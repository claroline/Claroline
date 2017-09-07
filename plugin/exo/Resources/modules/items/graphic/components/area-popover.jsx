import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {tex} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'

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
        className="area-popover"
        placement="bottom"
        positionLeft={this.props.left}
        positionTop={this.props.top}
        title={
          <div>
            {tex('graphic_area_edit')}

            <div className="popover-actions">
              <TooltipButton
                id="area-popover-delete"
                className="btn-link-default"
                title={tex('delete')}
                onClick={this.props.onDelete}
              >
                <span className="fa fa-fw fa-trash-o" />
              </TooltipButton>

              <TooltipButton
                id="area-popover-close"
                title={tex('close')}
                className="btn-link-default"
                onClick={this.props.onClose}
              >
                <span className="fa fa-fw fa-times" />
              </TooltipButton>
            </div>
          </div>
        }
      >
        <div className={classes('form-group', 'base-controls', {'form-last': !this.state.showFeedback})}>
          <div>
            <ColorPicker
              id="popover-color-picker"
              className="btn-default"
              color={this.props.color}
              onPick={color => this.props.onPickColor(color.hex)}
            />
          </div>

          <div className="right-controls">
            <input
              type="number"
              id="area-score"
              className="form-control area-score"
              value={this.props.score}
              onChange={e => this.props.onChangeScore(parseFloat(e.target.value))}
            />

            <TooltipButton
              id="area-popover-feedback-tip"
              className="btn-link-default"
              title={tex('graphic_feedback_info')}
              onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            >
              <span className="fa fa-fw fa-comments-o" />
            </TooltipButton>
          </div>
        </div>

        {this.state.showFeedback &&
          <FormGroup
            controlId="area-feedback"
            label={tex('feedback')}
            hideLabel={true}
            className="feedback-container form-last"
          >
            <Textarea
              id="graphic-popover-feedback"
              title={tex('feedback')}
              content={this.props.feedback}
              onChange={this.props.onChangeFeedback}
            />
          </FormGroup>
        }
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
  onClose: T.func.isRequired,
  onDelete: T.func.isRequired
}
