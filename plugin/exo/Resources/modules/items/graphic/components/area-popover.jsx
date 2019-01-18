import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {trans, tex} from '#/main/app/intl/translation'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'

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
              <Button
                id="area-popover-delete"
                className="btn-link-default"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash-o"
                label={trans('delete', {}, 'actions')}
                callback={this.props.onDelete}
                tooltip="top"
              />

              <Button
                id="area-popover-close"
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-times"
                label={trans('close', {}, 'actions')}
                callback={this.props.onClose}
                tooltip="top"
              />
            </div>
          </div>
        }
      >
        <div className={classes('form-group', 'base-controls', {'form-last': !this.state.showFeedback})}>
          <div>
            <ColorPicker
              id="popover-color-picker"
              className="btn-default"
              value={this.props.color}
              onChange={color => this.props.onPickColor(color)}
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

            <Button
              id="area-popover-feedback-tip"
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-comments-o"
              label={tex('graphic_feedback_info')}
              callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            />
          </div>
        </div>

        {this.state.showFeedback &&
          <FormGroup
            id="area-feedback"
            label={tex('feedback')}
            hideLabel={true}
            className="feedback-container form-last"
          >
            <Textarea
              id="graphic-popover-feedback"
              value={this.props.feedback}
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
