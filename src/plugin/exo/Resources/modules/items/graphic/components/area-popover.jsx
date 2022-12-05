import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {trans} from '#/main/app/intl/translation'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {ColorInput} from '#/main/theme/data/types/color/components/input'

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
          <Fragment>
            {trans('graphic_area_edit', {}, 'quiz')}

            <div className="popover-actions">
              <Button
                id="area-popover-delete"
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash"
                label={trans('delete', {}, 'actions')}
                callback={this.props.onDelete}
                tooltip="top"
                dangerous={true}
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
          </Fragment>
        }
      >
        <div className={classes('form-group', 'base-controls', {'form-last': !this.state.showFeedback})}>
          <ColorInput
            id="area-color"
            className="color"
            hideInput={true}
            value={this.props.color}
            onChange={this.props.onPickColor}
          />

          <div className="right-controls">
            {this.props.hasExpectedAnswers && !this.props.hasScore &&
              <input
                id="expected-answer"
                type="checkbox"
                checked={0 < this.props.score}
                onChange={(e) => this.props.onChangeScore(e.target.checked ? 1 : 0)}
              />
            }

            {this.props.hasExpectedAnswers && this.props.hasScore &&
              <input
                type="number"
                id="area-score"
                className="form-control score"
                value={this.props.score}
                onChange={e => this.props.onChangeScore(parseFloat(e.target.value))}
              />
            }

            <Button
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-comments"
              label={trans('graphic_feedback_info', {}, 'quiz')}
              callback={() => this.setState({showFeedback: !this.state.showFeedback})}
              tooltip="left"
            />
          </div>
        </div>

        {this.state.showFeedback &&
          <FormGroup
            id="area-feedback"
            label={trans('feedback', {}, 'quiz')}
            hideLabel={true}
            className="feedback-container form-last"
          >
            <HtmlInput
              id="area-feedback"
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
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  score: T.number.isRequired,
  feedback: T.string.isRequired,
  onChangeScore: T.func.isRequired,
  onChangeFeedback: T.func.isRequired,
  onPickColor: T.func.isRequired,
  onClose: T.func.isRequired,
  onDelete: T.func.isRequired
}
